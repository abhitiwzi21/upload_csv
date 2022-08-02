<?php namespace App\Http\Controllers\API; 
use App\Http\Controllers\Controller; 
use App\Models\File; use Validator; 
use App\Models\Data;
use Illuminate\Http\Request; 
class FileUploadController extends Controller { 
    public function upload(Request $request) 
    { 
        $validator = Validator::make($request->all(),[ 
              'file' => 'required|mimes:doc,docx,pdf,txt,csv|max:2048',
        ]);   
  
        if($validator->fails()) {          
             
            return response()->json(['error'=>$validator->errors()], 401);                        
         }  
  
   
        if ($file = $request->file('file')) {   
            $path = $file->store('public/files');
            // $name = $file->getClientOriginalName();
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $valid_extension = array("csv");

            // 2MB in Bytes
                $maxFileSize = 2097152; 
    

      // Check file extension
      if(in_array(strtolower($extension),$valid_extension)){

        // Check file size
        if($fileSize <= $maxFileSize){

          // File upload location
          $location = 'uploads';

          // Upload file
          $file->move($location,$filename);

          // Import CSV to Database
          $filepath = public_path($location."/".$filename);

          // Reading file
          $file = fopen($filepath,"r");

          $importData_arr = array();
          $i = 0;

          while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
             $num = count($filedata );
             
             // Skip first row
             if($i == 0){
                $i++;
                continue; 
             }
             for ($c=0; $c < $num; $c++) {
                $importData_arr[$i][] = $filedata [$c];
             }
             $i++;
          }
          fclose($file);
          // Insert to MySQL database
          $testResult= '';
          // Insert to MySQL database
          foreach($importData_arr as $importData){
           
            $validator = Validator::make($importData,[ 
                '1' => 'required',
                '2' => 'required',
                '3' => 'required|regex:/(.+)@(.+)\.(.+)/i',
                '4' => 'required',
                '5' => 'required',
          ],['1.required'=>'first name is Missing',
          '2.required'=>'Last name is Missing',
          '3.required'=>'Email is Missing',
          '4.required'=>'Gender is Missing',
          '5.required'=>'Ip Address is Missing'
        ]);   
    
          if($validator->fails()) {
                     
            $testResult .= $validator->errors()->tojson();
                                    
           } 
            $insertData = array(
                "first_name"=>$importData[1],
                "last_name"=>$importData[2],
                "email"=>$importData[3],
                "gender"=>$importData[4],
                "ip_address"=>$importData[5]);
            Data::insert($insertData);

          }
          return response()->json([
            "success" => true,
            "message" => "Import Successful",
            "Result" => $testResult


        ]);
          
        }else{
            return response()->json([
                "success" => false,
                "message" => "File too large. File must be less than 2MB."
            ]);
          
        }

      }else{
        return response()->json([
            "success" => false,
            "message" => "Invalid File Extension."
        ]);
        
      }
  
            //store your file into directory and db
            // $save = new File();
            // $save->name = $file;
            // $save->path= $path;
            // $save->save();
               
            // return response()->json([
            //     "success" => true,
            //     "message" => "File successfully uploaded",
            //     "file" => $file
            // ]);
            return response()->json([
                "success" => true,
                "message" => "Data Inserted successfully uploaded",
                "result"=>$testResult
            ]);
        }
  
   
    }
 

 

}