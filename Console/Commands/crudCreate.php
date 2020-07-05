<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class crudCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:create {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a view Create with crud system (Eg. > php artisan crud:create Todo)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*------------
            Variable declaration ($model, $table, $std, $file)
        ---------------------------------------*/
        $file   = 'create';
        $model  = $this->argument()['model'];

        // In case the Model ends with a Y
        $last_word = substr($model, -1);
        if($last_word == 'y'){
            $table = strtolower(substr($model, 0, -1)).'ies';
        }else{
            $table = strtolower($model).'s';
        }
        $std = DB::select('describe '. $table);

        /*------------
            Create new folder
        ---------------------------------------*/
        $path_name = base_path('resources/views/'.$table); 
        if (!is_dir($path_name)) {
            mkdir($path_name, 0700,true);
        }

        /*------------
            Duplicate And Move File
        ---------------------------------------*/
        $skeleton_file = base_path('app/Console/Skeletons/'.$file.'.blade.php');
        $new_file      = $path_name.'/'.$file.'.blade.php';
        copy($skeleton_file, $new_file);

        /*------------
            Handle file
        ---------------------------------------*/
        // Get All Fields
        $form_group = [];

        foreach($std as $k => $v){

            $pos = strpos($v->Type, 'varchar');

            if($pos !== false){
                $form_group[] = '
                <div class="form-group">
                    <label for="'.$v->Field.'">'.$v->Field.'</label>
                    <input type="text" name="'.$v->Field.'" class="form-control @error(\''.$v->Field.'\') is-invalid @enderror" value="{{@old(\''.$v->Field.'\')}}" id="input'.$v->Field.'" aria-describedby="'.$v->Field.'Help">
                    @error("'.$v->Field.'")
                    <span class="invalid-feedback">{{$message}}</span>
                    @enderror
                </div>';
            }

        }

        // Open File for reading and modification
        $text=fopen($new_file,'r');
        $contenu=file_get_contents($new_file);
        $contenuMod=str_replace(
            ['_table_', '_form_group_', '_model_'],
            [$table, implode('',$form_group), $model],
            $contenu
        );
        fclose($text);

        // ReWrite modification
        $text2=fopen($new_file,'w+');
        fwrite($text2,$contenuMod);
        fclose($text2);

        echo 'Success';
    }
}