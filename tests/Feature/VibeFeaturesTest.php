<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,Tweet,Message};
use Illuminate\Foundation\Testing\RefreshDatabase;
class VibeFeaturesTest extends TestCase {
 use RefreshDatabase;
 private function person(string $name): User {return User::create(['name'=>$name,'username'=>$name,'email'=>$name.'@example.test','password'=>'password123']);}
 public function test_chat_is_private_and_persists(): void {
  $a=$this->person('alpha');$b=$this->person('beta');$c=$this->person('gamma');
  $this->get('/messages')->assertRedirect('/login');
  $this->actingAs($a)->post('/messages/'.$b->id,['body'=>'Hello <script>alert(1)</script>'])->assertRedirect('/messages/'.$b->id);
  $this->assertDatabaseHas('messages',['sender_id'=>$a->id,'recipient_id'=>$b->id]);
  $this->actingAs($b)->get('/messages/'.$a->id.'/poll')->assertJsonCount(1);
  $this->actingAs($c)->get('/messages/'.$a->id.'/poll')->assertExactJson([]);
  $this->actingAs($b)->get('/messages/'.$a->id)->assertOk()->assertSee('&lt;script&gt;',false);
  $this->actingAs($a)->postJson('/messages/'.$b->id,['body'=>''])->assertUnprocessable();
  $this->actingAs($a)->postJson('/messages/'.$a->id,['body'=>'self'])->assertUnprocessable();
 }
 public function test_bookmarks_are_personal_and_idempotent(): void {
  $a=$this->person('alpha');$b=$this->person('beta');$t=Tweet::create(['user_id'=>$a->id,'content'=>'Saved idea']);
  $this->actingAs($a)->get('/bookmarks')->assertOk()->assertSee('Belum ada postingan tersimpan');
  $this->actingAs($a)->postJson('/bookmarks/'.$t->id)->assertJson(['saved'=>true]);
  $this->postJson('/bookmarks/'.$t->id)->assertOk();$this->assertDatabaseCount('bookmarks',1);
  $this->get('/bookmarks')->assertSee('Saved idea')->assertDontSee('Belum ada postingan tersimpan');
  $this->actingAs($b)->get('/bookmarks')->assertDontSee('Saved idea');
  $this->actingAs($a)->deleteJson('/bookmarks/'.$t->id)->assertJson(['saved'=>false]);
  $this->assertDatabaseCount('bookmarks',0);
 }
 public function test_bookmarks_are_ordered_by_when_they_were_saved(): void {
  $a=$this->person('saved_order');
  $older=Tweet::create(['user_id'=>$a->id,'content'=>'Older save']);
  $newer=Tweet::create(['user_id'=>$a->id,'content'=>'Newer save']);
  \Illuminate\Support\Facades\DB::table('bookmarks')->insert([
   ['user_id'=>$a->id,'tweet_id'=>$older->id,'created_at'=>now()->subMinute(),'updated_at'=>now()->subMinute()],
   ['user_id'=>$a->id,'tweet_id'=>$newer->id,'created_at'=>now(),'updated_at'=>now()],
  ]);
  $response=$this->actingAs($a)->get('/bookmarks')->assertOk()->assertSee('2 postingan');
  $response->assertViewHas('tweets',fn($tweets)=>$tweets->first()->is($newer));
 }
 public function test_existing_pages_and_following_feed(): void {
  $a=$this->person('alpha');$b=$this->person('beta');
  Tweet::create(['user_id'=>$b->id,'content'=>'Followed story']);
  $this->get('/')->assertOk()->assertSee('Followed story');
  $this->get('/login')->assertOk();$this->get('/register')->assertOk();$this->get('/forgot-password')->assertOk();
  $this->actingAs($a)->get('/?feed=following')->assertDontSee('Followed story');
  $this->post('/follow/'.$b->id)->assertRedirect();
  $this->get('/?feed=following')->assertSee('Followed story');
  foreach(['/profile','/profile/'.$b->id,'/tags','/search?q=story','/messages','/bookmarks'] as $url)$this->get($url)->assertOk();
 }
 public function test_posts_likes_comments_and_escaping(): void {
  $a=$this->person('alpha');
  $this->actingAs($a)->post('/tweets',['content'=>'My post <script>alert(1)</script> #vibe'])->assertRedirect();
  $t=Tweet::first();$this->get('/')->assertOk()->assertSee('&lt;script&gt;',false);
  $this->postJson('/tweets/'.$t->id.'/like')->assertOk();
  $this->assertDatabaseHas('likes',['user_id'=>$a->id,'tweet_id'=>$t->id]);
  $this->postJson('/tweets',['content'=>'A comment','quote_tweet_id'=>$t->id])->assertOk();
  $comment=Tweet::where('quote_tweet_id',$t->id)->first();
  $this->postJson('/tweets',['content'=>'A nested reply','quote_tweet_id'=>$t->id,'parent_comment_id'=>$comment->id])->assertOk();
  $this->assertSame(1,$t->quotes()->whereNull('parent_comment_id')->count());
  $this->assertSame(1,$comment->replies()->count());
  $this->get('/tweets/'.$t->id.'/comments')->assertOk()->assertSee('A nested reply')->assertSee('data-depth="1"',false)->assertDontSee('Topik populer');
  $this->get('/tags/vibe')->assertOk();
  $this->putJson('/tweets/'.$t->id,['content'=>'Edited post'])->assertOk();
  $this->assertDatabaseHas('tweets',['id'=>$t->id,'content'=>'Edited post']);
 }
 public function test_poll_cursor_filters_both_directions_and_never_replays_old_messages(): void {
  $a=$this->person('cursor_a');$b=$this->person('cursor_b');$c=$this->person('cursor_c');
  $one=Message::create(['sender_id'=>$a->id,'recipient_id'=>$b->id,'body'=>'outgoing']);
  $two=Message::create(['sender_id'=>$b->id,'recipient_id'=>$a->id,'body'=>'incoming']);
  Message::create(['sender_id'=>$c->id,'recipient_id'=>$a->id,'body'=>'private']);
  $url='/messages/'.$b->id.'/poll';
  $this->actingAs($a)->getJson($url.'?after='.$one->id)->assertJsonCount(1)->assertJsonPath('0.id',$two->id);
  for($i=0;$i<4;$i++)$this->getJson($url.'?after='.$two->id)->assertExactJson([]);
  $new=Message::create(['sender_id'=>$a->id,'recipient_id'=>$b->id,'body'=>'new outgoing']);
  $this->getJson($url.'?after='.$two->id)->assertJsonCount(1)->assertJsonPath('0.id',$new->id);
  $this->getJson($url.'?after='.$new->id)->assertExactJson([]);
  $this->getJson($url.'?after=-1')->assertUnprocessable();
 }
 public function test_history_is_paginated_without_repeating_outgoing_messages(): void {
  $a=$this->person('history_a');$b=$this->person('history_b');
  for($i=1;$i<=55;$i++)Message::create(['sender_id'=>$a->id,'recipient_id'=>$b->id,'body'=>'history-'.$i]);
  $response=$this->actingAs($a)->get('/messages/'.$b->id.'?messages_page=2')->assertOk();
  $response->assertSee('<span>history-1</span>',false)->assertDontSee('<span>history-55</span>',false);
  $response->assertViewHas('messages',fn($messages)=>$messages->count()===5 && $messages->total()===55);
  $this->get('/messages/'.$b->id.'?contacts_page=1')->assertViewHas('messages',fn($messages)=>$messages->currentPage()===1 && $messages->count()===50);
 }
 public function test_ajax_send_returns_one_message_and_does_not_advance_a_clients_poll_cursor(): void {
  $a=$this->person('ajax_a');$b=$this->person('ajax_b');
  $incoming=Message::create(['sender_id'=>$b->id,'recipient_id'=>$a->id,'body'=>'concurrent incoming']);
  $this->actingAs($a)->postJson('/messages/'.$b->id,['body'=>'outgoing'])->assertCreated()->assertJsonPath('body','outgoing');
  $this->getJson('/messages/'.$b->id.'/poll?after=0')->assertJsonCount(2)->assertJsonPath('0.id',$incoming->id);
  $this->assertDatabaseCount('messages',2);
 }
 public function test_account_navigation_and_profile_form_do_not_duplicate_or_expose_storage_paths(): void {
  $a=$this->person('settings_a');$a->update(['avatar'=>'avatars/local.jpg']);
  $this->actingAs($a)->get('/profile')->assertOk()->assertSee('id="account-toggle"',false)->assertDontSee('brand-mark')->assertDontSee('RUANG BERTEMU IDE')->assertDontSee('value="avatars/local.jpg"',false);
  $this->patch('/profile',['name'=>'Updated name','email'=>$a->email,'bio'=>'New bio'])->assertRedirect('/profile');
  $this->assertDatabaseHas('users',['id'=>$a->id,'name'=>'Updated name','avatar'=>'avatars/local.jpg']);
 }
}
