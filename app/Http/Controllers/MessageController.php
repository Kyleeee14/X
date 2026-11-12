<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private function thread(int $me, int $other)
    {
        // Group BOTH directions before applying cursor or pagination constraints.
        return Message::where(function ($conversation) use ($me, $other) {
            $conversation->where(function ($q) use ($me, $other) {
                $q->where('sender_id', $me)->where('recipient_id', $other);
            })->orWhere(function ($q) use ($me, $other) {
                $q->where('sender_id', $other)->where('recipient_id', $me);
            });
        });
    }

    public function index(Request $request, ?User $user = null)
    {
        $me = $request->user()->id;
        abort_if($user && $user->id === $me, 404);
        $q = mb_substr((string) $request->query('q', ''), 0, 100);
        $lastMessage = Message::select('id')->where(function ($conversation) use ($me) {
            $conversation->where(fn ($q) => $q->whereColumn('sender_id', 'users.id')->where('recipient_id', $me))
                ->orWhere(fn ($q) => $q->whereColumn('recipient_id', 'users.id')->where('sender_id', $me));
        })->latest('id')->limit(1);
        $people = User::select('users.*')->addSelect(['last_message_id' => $lastMessage])
            ->where('id', '!=', $me)
            ->when($q !== '', fn ($query) => $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%")))
            ->orderByDesc('last_message_id')->orderBy('name')->paginate(20, ['*'], 'contacts_page')->withQueryString();
        if ($user?->is_admin && ! $this->thread($me, $user->id)->exists()) {
            Message::create(['sender_id' => $user->id, 'recipient_id' => $me, 'body' => 'How can I help you? Pilih salah satu bantuan di bawah untuk memulai.']);
        }
        $messages = $user ? $this->thread($me, $user->id)->latest('id')->paginate(50, ['*'], 'messages_page')->withQueryString() : null;
        if ($user) {
            Message::where('sender_id', $user->id)->where('recipient_id', $me)->whereNull('read_at')->update(['read_at' => now()]);
        }
        $previews = Message::whereIn('id', $people->pluck('last_message_id')->filter())->get()->keyBy('id');
        return view('messages.index', compact('people', 'user', 'messages', 'previews'));
    }

    public function poll(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 404);
        $request->validate(['after' => 'nullable|integer|min:0']);
        $messages = $this->thread($request->user()->id, $user->id)
            ->where('id', '>', (int) $request->query('after', 0))->orderBy('id')->limit(100)
            ->get(['id', 'sender_id', 'body', 'created_at']);
        Message::where('sender_id', $user->id)->where('recipient_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json($messages)->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422);
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $message = Message::create(['sender_id' => $request->user()->id, 'recipient_id' => $user->id, 'body' => $data['body']]);
        if ($user->is_admin && ! $request->user()->is_admin) {
            Message::create(['sender_id' => $user->id, 'recipient_id' => $request->user()->id, 'body' => $this->automatedReply($data['body'])]);
        }
        if ($request->wantsJson()) {
            return response()->json($message->only(['id', 'sender_id', 'body', 'created_at']), 201);
        }
        return redirect()->route('messages.show', $user);
    }

    private function automatedReply(string $body): string
    {
        $text = mb_strtolower(trim($body));
        return match (true) {
            str_contains($text, 'laporkan postingan'), str_contains($text, 'lapor postingan') => 'Silakan kirim link postingan dan jelaskan alasan pelaporannya. Tim moderasi akan memeriksa konteks sebelum mengambil tindakan.',
            str_contains($text, 'masalah akun'), str_contains($text, 'akun') => 'Jelaskan masalah akun yang kamu alami. Jangan pernah mengirim kata sandi, kode OTP, atau data rahasia melalui chat.',
            str_contains($text, 'laporkan bug'), str_contains($text, 'bug'), str_contains($text, 'error') => 'Sebutkan halaman, tindakan terakhir, dan pesan error yang muncul. Jika ada, sertakan tangkapan layar tanpa data pribadi.',
            str_contains($text, 'panduan fitur'), str_contains($text, 'cara') => 'Sebutkan fitur yang ingin kamu gunakan: postingan, media, komentar, bookmark, profil, atau pesan.',
            default => 'Pesanmu sudah diterima. Pilih kategori bantuan agar saya dapat mengarahkan laporan dengan lebih tepat.',
        };
    }
}
