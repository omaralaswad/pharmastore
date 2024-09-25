<?php

namespace App\Http\Controllers;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ImageController extends Controller
{
    public function create(Request $req){
        return view('upload');
     }
 
 
 
     public function store(Request $request){
         //view('upload');
          $size=$request->file('image')->getSize();
          $name=$request->file('image')->getClientOriginalName();        
          $file = Storage::put('public/storge/images', $request->file('image'));
          $path = Storage::url($file);
          $photo = new Image();
          $photo->name = $path;
          $photo->size = $size;
          $photo->save();
          return redirect()->back();
         
     } 
}
