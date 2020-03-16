<?php

namespace App\Http\Controllers\User;

use App\Contacts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    //
    protected $contacts;
    protected $base_url;
    protected $default_avatar;
    private $image_data;

    public function __contruct(UrlGenerator $urlGenerator)
    {
        $this->middleware('auth:users');
        $this->contacts = new Contacts();
        $this->default_avatar = 'default_avatar.png';
        $this->base_url = $urlGenerator->to('/');
        $this->image_data = array('file_name'=>'default_avatar.png','base64'=>'');

    }

    /**
     * Creates contacts
     */

    public function addContacts(Request $request)
    {
        // return $this->image_data['file_name'];
        return "$this->link";

        $validator = Validator::make($request->all(),[
            'token'=>'required',
            'firstname'=>'required',
            'phonenumber'=>'required|string'
        ]);


        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>$validator->messages()->toArray()
            ],400);
        }

        if($request->hasFile('profile_image'))
        {
            $this->processImage($request->profile_image);
            file_put_contents('/profile_images/'.$this->image_data['file_name'],$this->image_data['base64']);

        }

        // Get user from the auth token
        $user = $this->__authenticate($request->token);

        $dummy = $user->contacts()->save(new Contacts([
            'firstname'=>$request->firstname,
            'lastname'=>$request->lastname??null,
            'email'=>$request->email??null,
            'phonenumber'=>$request->phonenumber,
            'image_file'=>$this->image_data['file_name'],
            ])
        );

        return response()->json([
            'success'=>true,
            'message'=>'contact saved successfully'
        ],200);



    }

    /**
     * Get paginated contacts
     */

    public function getPaginatedContacts($token,$pagination=null)
     {

         $user = $this->__authenticate($token);

         if(is_null($pagination) or empty($pagination))
         {
             $contacts = $user->contacts->orderBy('id','DESC')->get()->toArray();
         }else{

             $contacts = $user->contacts->orderBy('id','DESC')->paginate($pagination);
         }


        return response()->json([
            'success'=>true,
            'data'=>$contacts,
            'file_directory'=>$this->base_url.'/profile_images'
        ],200);


     }


     /**
      * Updates Contact
      */

    public function updateContact(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'token'=>'required',
            'firstname'=>'required',
            'phonenumber'=>'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>$validator->messages()->toArray()
            ],400);
        }

        $user = $user = $this->__authenticate($request->token);
        $contact = $this->contacts::find($id);
        if(!$contact)
        {
            return response()->json([
                'success'=>false,
                'message'=>'Contact not found'
            ],404);
        }

        if($request->hasFile('profile_image'))
        {
            $this->processImage($request->profile_image);
            file_put_contents('/profile_images/'.$this->image_data['file_name'],$this->image_data['base64']);
            unlink($this->base_url.'/profile_images/'.$contact->image_file);

        }

        $user->contcts()->where('id',$contact->id)->update([
            'firstname'=>$request->firstname,
            'lastname'=>$request->lastname??null,
            'email'=>$request->email??null,
            'phonenumber'=>$request->phonenumber,
            'image_file'=>$this->image_data['file_name']
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'contact updated successfully'
        ],200);

    }


    /**
     * Deletes the Contact
     *
     */
    public function deleteContact($id,$token)
    {
        $user = $this->__authenticate($token);
        $contact = $this->contacts::find($id);

        if(!$contact)
        {
            return response()->json([
                'success'=>false,
                'message'=>'Contact not found'
            ],404);
        }

        if($user->contacts()->delete())
        {
            unlink($this->base_url.'/profile_images/'.$contact->image_file);

        }

        return response()->json([
            'success'=>true,
            'message'=>'contact deleted successfully'
        ],200);


    }

    /**
     * Get single Contact
     */

    public function getContact($id)
    {
        $contact = $this->contacts::find($id);

        if(!$contact)
        {
            return response()->json([
                'success'=>false,
                'message'=>'contact not found'
            ],404);
        }

        return response()->json([
            'success'=>true,
            'data'=>$contact,
            'file_directory'=>$this->base_url.'/profile_images'
        ],200);
    }

    public function searchContacts($search,$token, $pagination=null)
    {
        $user = $this->__authenticate($token);

         if(is_null($pagination) or empty($pagination))
         {
             $contacts = $user->contacts->where(function($query) use ($search){
                 $query->where('firstname','LIKE',"%$search%")
                 ->orWhere('lastname','LIKE',"%$search%")
                 ->orWhere('email','LIKE',"%$search%")
                 ->orWhere('phonenumber','LIKE',"%$search%");
             })->orderBy('relevance')->get()->toArray();
         }else{

             $contacts = $user->contacts->where(function($query) use ($search){
                $query->where('firstname','LIKE',"%$search%")
                ->orWhere('lastname','LIKE',"%$search%")
                ->orWhere('email','LIKE',"%$search%")
                ->orWhere('phonenumber','LIKE',"%$search%");
            })->orderBy('relevance')->paginate($pagination);
         }

        return response()->json([
            'success'=>true,
            'data'=>$contacts,
            'file_directory'=>$this->base_url.'/profile_images'
        ],200);



    }





    private function __authenticate($token)
    {
        return auth('users')->authenticate($token);
    }

    private function processImage($image)
    {
        $fileBin = file_get_contents($image);
        $mime_type = mime_content_type($image);
        $unique_name = uniqid().'_'.time().'_'.date('Ymd');

        if('image/png' == $mime_type)
        {
            $unique_name.='.png';
        }
        else if('image/jpeg' == $mime_type)
        {
            $unique_name.='.jpeg';
        }
        else if('image/jpg' == $mime_type)
        {
            $unique_name.='.jpg';
        }
        else{
            return response()->json([
                'success'=>false,
                'message'=>'only png, jpg and jpeg files are accepted for contact images'
            ]);
        }

        $this->image_data = [
            'file_name'=>$unique_name,
            'base64'=>$fileBin
        ];
    }
}
