<?php
namespace App\Http\Controllers;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class BookmarkController extends Controller {
    public function index(Request $request) {
        $tweets = Tweet::with(['user','likes'])->whereIn('id', DB::table('bookmarks')->where('user_id',$request->user()->id)->select('tweet_id'))->latest()->paginate(20);
        return view('bookmarks.index', compact('tweets'));
    }
    public function store(Request $request, Tweet $tweet) {
        DB::table('bookmarks')->insertOrIgnore(['user_id'=>$request->user()->id,'tweet_id'=>$tweet->id,'created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['saved'=>true]);
    }
    public function destroy(Request $request, Tweet $tweet) {
        DB::table('bookmarks')->where('user_id',$request->user()->id)->where('tweet_id',$tweet->id)->delete();
        return response()->json(['saved'=>false]);
    }
}
