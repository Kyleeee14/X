<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tweets:deduplicate {--dry-run : Hanya tampilkan jumlah tanpa menghapus}')] 
#[Description('Hapus postingan utama yang isinya berulang dan pertahankan postingan paling awal')]
class DeduplicateTweets extends Command
{
    public function handle(): int
    {
        $seen = [];
        $duplicateIds = [];

        Tweet::query()
            ->whereNull('quote_tweet_id')
            ->whereNull('parent_comment_id')
            ->whereNull('original_tweet_id')
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('id')
            ->get(['id', 'user_id', 'content'])
            ->each(function (Tweet $tweet) use (&$seen, &$duplicateIds): void {
                $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $tweet->content)));
                $key = $tweet->user_id.'|'.$normalized;

                if (isset($seen[$key])) {
                    $duplicateIds[] = $tweet->id;
                    return;
                }

                $seen[$key] = $tweet->id;
            });

        if ($duplicateIds === []) {
            $this->info('Tidak ada tweet duplikat.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info(count($duplicateIds).' tweet duplikat ditemukan. Belum ada data yang dihapus.');
            return self::SUCCESS;
        }

        foreach (array_chunk($duplicateIds, 200) as $ids) {
            Tweet::whereIn('id', $ids)->delete();
        }

        $this->info(count($duplicateIds).' tweet duplikat dihapus. Satu tweet paling awal dari setiap isi tetap disimpan.');
        return self::SUCCESS;
    }
}
