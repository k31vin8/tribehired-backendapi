<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller as BaseController;

use App\Models\Post;
use App\Models\Comment;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function TopPost(){
        $posts = $this->getPosts();
        $comments = $this->getComments();
        $result = [];
        
        $thePosts =    $posts->each(function($item, $key) use ($comments){
                        $item->numberOfComments = $comments->where('postId', $item->id)->count();
                    })->sortByDesc('numberOfComments');

        foreach($thePosts as $post){
            $result[] =[
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_body' => $post->body,
                'total_number_of_comments' =>$post->numberOfComments,  
            ];
        }
        
        return response()->json($result);
    }

    public function searchPost($keyword){
        $comments = $this->getComments();
        
        if (is_numeric($keyword)){
            $thecomments = $comments->filter(function($item, $key) use ($keyword) {
            //assumption search by post id
                if ($item->postId == $keyword){
                    return true;
                }
            });
        }else if(str_contains($keyword,'.') && str_contains($keyword,'@')){
            //assumption search by email
            $thecomments = $comments->filter(function($item, $key) use ($keyword) {
                if (str_contains($item->email,$keyword)){
                    return true;
                }
            });
        }else{
            //assumption otherwise search by user name (if contain)
            $thecomments = $comments->filter(function($item, $key) use ($keyword) {
                if (str_contains($item->name,$keyword)){
                    return true;
                }
            });
        }
        return response()->json($thecomments);
    }

    private function getPosts(){
        $PostArray = json_decode(Http::get('https://jsonplaceholder.typicode.com/posts'), true);
        return Post::hydrate($PostArray);
    }

    private function getComments(){
        $CommentArray = json_decode(Http::get('https://jsonplaceholder.typicode.com/comments'), true);
        return Comment::hydrate($CommentArray);
    }
}
