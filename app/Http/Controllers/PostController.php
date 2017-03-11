<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Post;
use App\Category;
use Session;
use Image;

class PostController extends Controller
{
   public function __construct(){
     $this->middleware('auth');
   }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //storing all blog post in one varible
        $posts = Post::orderBy('id', 'desc')->paginate(5);
        return view('posts.index')->withPosts($posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
          $categories = Category::all();
          return view('posts.create')->withCategories($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate the Data
        $this->validate($request, array(
          'title'=> 'required|max:255',
          'slug' => 'required|alpha_dash|min:5|max:255|unique:posts,slug',
          'category_id' => 'required|integer',
          'body' => 'required'
        ));
        //store database
        $post = new Post;
        $post->title = $request->title;
        $post->slug = $request->slug;
        $post->category_id = $request->category_id;
        $post->body = $request->body;


        if ($request->hasFile('featured_img')) {
          $image = $request->file('featured_img');
          $filename = time() . '.' . $image->getClientOriginalExtension();
          $location = public_path('images/' . $filename);
          Image::make($image)->resize(800, 400)->save($location);

          $post->image = $filename;
          }


        $post->save();
        Session::flash('success', 'The blog post is successfully saved');
        //redirection
        return redirect()->route('posts.show', $post->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id); //find is just like a where command
        return view('posts.show')->withPost($post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
          $categories = Category::all();
          $cats = array();
          foreach ($categories as $category) {
              $cats[$category->id] = $category->name;
          }
          $post = Post::find($id); //find is just like a where command
          return view('posts.edit')->withPost($post)->withCategories($cats);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      // Validate the data
      $post = Post::find($id);

      if ($request->input('slug') == $post->slug) {
          $this->validate($request, array(
              'title' => 'required|max:255',
              'category_id' => 'required|integer',
              'body'  => 'required'
          ));
      } else {
      $this->validate($request, array(
              'title' => 'required|max:255',
              'slug'  => 'required|alpha_dash|min:5|max:255|unique:posts,slug',
              'category_id' => 'required|integer',
              'body'  => 'required'
          ));
      }

      // Save the data to the database
      $post = Post::find($id);

      $post->title = $request->input('title');
      $post->slug = $request->input('slug');
      $post->body = $request->input('body');
      $post->category_id = $request->input('category_id');
    //  $post->body = Purifier::clean($request->input('body'));

      $post->save();


      Session::flash('success', 'The blog post is successfully updated');
      return redirect()->route('posts.show', $post->id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        $post->delete();
        Session::flash("success","The post was successfully deleted");

        return redirect()->route('posts.index');
    }
}
