<?php

use RainLab\Blog\Models\Post;

Route::get('blog/api', function () {
    $blogPosts = Post::paginate(15);

    return $blogPosts;
});

Route::get('blog/api/{id}', function ($id) {
    $blogPosts = Post::findOrFail($id);

    return $blogPosts;
});