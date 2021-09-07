<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Course;

class CourcesController extends Controller
{
    public function index()
    {
        $courses = Course::all();

        return response()->json(['msg' => '', 'data' => $courses, 'error' => false],200);
    }
        
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:courses,name',
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video' => 'required_without_all:pdf|mimes:mp4,mov,ogg,qt | max:2048',
            'pdf' => 'required_without_all:video|mimes:pdf|max:2048',
            'price' => 'nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }

        $user = Auth::user();

        $corce = new Course();

        $corce->user_id = $user->id;
        $corce->name = $request->name;
        
        

        if($request->hasFile('image')){
            $img = $request->file('image');
            $file_name = 'cource_'.time().'.'.$img->extension();      
            $path = $img->storeAs('images', $file_name, 'public');
            $corce->image = $file_name;
        }
        
        if($request->hasFile('video')){
            $img = $request->file('video');
            $file_name = 'cource_'.time().'.'.$img->extension();      
            $path = $img->storeAs('videos', $file_name, 'public');

            $corce->video = $file_name;    
        }
    
        if($request->hasFile('pdf')){
            $img = $request->file('pdf');
            $file_name = 'cource_'.time().'.'.$img->extension();      
            $path = $img->storeAs('pdfs', $file_name, 'public');

            $corce->pdf = $file_name;
        }
        if($request->price != ''){
            $corce->price = $request->price;
        }
        if($request->details != ''){
            $corce->details = $request->details;
        }

          $corce->save();

          return response()->json(['msg' => 'Course add successfully', 'data' => $corce, 'error' => false], 200);
    }
    
    public function show($id)
    {
        $course = Course::where('id', $id)->first();
        $course->makeVisible(['video','pdf']);

        return response()->json(['msg' => '', 'data' => $course, 'error' => false],200);
    }
    
    public function edit($id)
    {
        //
    }
    
    public function update(Request $request, $id)
    {
        //
    }
    
    public function destroy($id)
    {
        //
    }
}
