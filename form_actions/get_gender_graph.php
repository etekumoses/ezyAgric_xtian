<?php
#includes
session_start();
require_once dirname(dirname(__FILE__)) . "/php_lib/user_functions/json_models_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/user_functions/crud_functions_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/database_query_processor_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/utility_class.php";
//id: id, district: district,country :country,parish : parish , village : village
/*$_POST['id']=5;
$_POST['district']='all';
$_POST['country']='all';
$_POST['parish']='all';
$_POST['village']='all';
$_POST['production']='all';
$_POST['gender']="all";*/

$mCrudFunctions = new CrudFunctions();
$json_model_obj = new JSONModel();
$util_obj = new Utilties();

$va = $_POST['va'];
$va = str_replace("(", "-", $va);
$va = str_replace(")", "", $va);
//$string="hyderabad-india";
$strings = explode('-', $va);
//echo $strings[0]; // outputs: hyderabad
//echo "<br>"; // new line
$va = strtolower($strings[1]); //

$age_min = !empty($_POST['age_min']) ? (int)$_POST['age_min'] : 0;
$age = !empty($_POST['age']) ? (int)$_POST['age'] : 0;
$age_max = !empty($_POST['age_max']) ? (int)$_POST['age_max'] : 0;
$ageFilter = "";
//floor(DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),DATE(biodata_farmer_dob))/365.25)
if ($age != "" && $age != 0) {
    $ageFilter = " floor(DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),DATE(biodata_farmer_dob))/365.25)=$age  AND ";
}

if ($age_min <= $age_max && $age_max != 0) {
    $ageFilter = "  (floor(DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),DATE(biodata_farmer_dob))/365.25) BETWEEN $age_min AND $age_max )  AND ";
}

if ($age_min > 0 && $age_max == 0) {
    $ageFilter = " (floor(DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),DATE(biodata_farmer_dob))/365.25) > $age_min )  AND ";
}

//header('Content-type: application/json');
$vasql = " AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'";
$data = array();
$branch = $_SESSION['user_account'];
$role =  $_SESSION['role'];

if($_SESSION["account_name"] == "Rushere SACCO"){
   if($role == 1){
       if (isset($_POST['id']) && isset($_POST['district']) && isset($_POST['country']) && isset($_POST['parish']) && isset($_POST['village']))
       {

           $district = $_POST['district'];
           $subcounty = $_POST['country'];
           $parish = $_POST['parish'];
           $id = $_POST['id'];

           ///////////////////////////////////////districts
           if ($_POST['district'] == "all") {
               $table = "dataset_" . $_POST['id'];
               $rows = array();

               $male = 0;
               $female = 0;
               if ($_POST['production'] == "all") {

                   if ($_POST['gender'] == "all") {

                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                       if ($_POST['va'] == "all") {

                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_gender) = 'female' ");

                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_gender) = 'male'  ");
                           $total_farmers = $female + $male;

                       } else {
                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_gender) = 'female' " . $vasql);

                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_gender) = 'male'   " . $vasql);
                       }

                   } else {

                       $gender = $_POST['gender'];

                       if ($gender == "male") {
                           $male = 100;
                       } else {
                           $female = 100;
                       }
                   }

               }
               else {
                   $string = $_POST['production'];
                   if (strpos($string, "productionyes") === false) {
                       if (strpos($string, "productionno") === false) {
                           if (strpos($string, "generalyes") === false) {
                               if (strpos($string, "generalno") === false) {
                               } else {

                                   $p_id = str_replace("generalno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {//$vasql

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' ");


                                       } else {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                       }

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                   }
                               }

                           } else {

                               $p_id = str_replace("generalyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {

                           $p_id = str_replace("productionno", "", $string);
                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];

                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' ");


                               } else {

                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                               }


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }

                           }
                       }

                   } else {

                       $p_id = str_replace("productionyes", "", $string);

                       //
                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                       $column = $row[0]['columns'];
                       if ($_POST['gender'] == "all") {

                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                           if ($_POST['va'] == "all") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' ");

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' ");

                           } else {//.$vasql

                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                           }


                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = 100;
                           } else {
                               $female = 100;
                           }


                       }
                   }


               }

               //echo $female;
               draw_pie_chart($male, $female, $json_model_obj, $util_obj);


           } else


               ///////////////////////////////////////////subcounties
               if ($_POST['country'] == "all") {

                   $table = "dataset_" . $_POST['id'];
                   $district = $_POST['district'];
                   $rows = array();
                   if ($_POST['production'] == "all") {

                       if ($_POST['gender'] == "all") {

                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                           if ($_POST['va'] == "all") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male'");

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female'");
                               $total_farmers = $female + $male;

                           } else {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male'" . $vasql);

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female'" . $vasql);


                           }//.$vasql


                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = 100;
                           } else {
                               $female = 100;
                           }


                       }


                   } else {
                       $string = $_POST['production'];
                       if (strpos($string, "productionyes") === false) {
                           if (strpos($string, "productionno") === false) {
                               if (strpos($string, "generalyes") === false) {
                                   if (strpos($string, "generalno") === false) {

                                   } else {
                                       $p_id = str_replace("generalno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }


                                       }

                                   }
                               } else {
                                   $p_id = str_replace("generalyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }
                               }


                           } else {
                               $p_id = str_replace("productionno", "", $string);

                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {
                           $p_id = str_replace("productionyes", "", $string);

                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];
                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                               } else {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                               }//.$vasql


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }


                           }

                       }


                   }


                   draw_pie_chart($male, $female, $json_model_obj, $util_obj);


               } else

                   ///////////////////////////////////////////parish

                   if ($_POST['parish'] == "all") {

                       $table = "dataset_" . $_POST['id'];
                       $subcounty = $_POST['country'];
                       $rows = array();
                       if ($_POST['production'] == "all") {
                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' ");


                               } else {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' " . $vasql);


                               }


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }

                           }
                       } else {
                           $string = $_POST['production'];
                           if (strpos($string, "productionyes") === false) {
                               if (strpos($string, "productionno") === false) {
                                   if (strpos($string, "generalyes") === false) {
                                       if (strpos($string, "generalno") === false) {
                                       } else {
                                           $p_id = str_replace("generalno", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {


                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }


                                           }
                                       }


                                   } else {
                                       $p_id = str_replace("generalyes", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }

                               }
                           } else {
                               $p_id = str_replace("productionyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }

                               }


                           }
                       }

                       draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                   } else

                       ///////////////////////////////////////////village

                       if ($_POST['village'] == "all") {

                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' ");


                                   } else {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' " . $vasql);

                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }

                               }
                           } else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                                   } else {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }


                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }

                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }

                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }


                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                       }
                       else
                       {

                           //echo "off";
                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $village = $_POST['village'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];

                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           } else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'");

                                                   } else {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }

                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {

                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql

                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }
                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                           } else {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql

                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }

                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                       }

       }
       else {
           $util_obj->deliver_response(200, 0, null);
       }
   }
   elseif($role == 2){
       if (isset($_POST['id']) && isset($_POST['district']) && isset($_POST['country']) && isset($_POST['parish']) && isset($_POST['village']))
       {

           $district = $_POST['district'];
           $subcounty = $_POST['country'];
           $parish = $_POST['parish'];
           $id = $_POST['id'];

           ///////////////////////////////////////districts
           if ($_POST['district'] == "all") {
               $table = "dataset_" . $_POST['id'];
               $rows = array();

               $male = 0;
               $female = 0;
               if ($_POST['production'] == "all") {

                   if ($_POST['gender'] == "all") {

                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                       if ($_POST['va'] == "all") {

                           $female = (int)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");

                           $male = (int)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");
                           $total_farmers = $female + $male;

                       } else {
                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_gender) = 'female' " . $vasql);

                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_gender) = 'male'   " . $vasql);
                       }

                   } else {

                       $gender = $_POST['gender'];

                       if ($gender == "male") {
                           $male = (int)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");

                       } else {
                           $female = (int)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");

                       }
                   }

               }
               else {
                   $string = $_POST['production'];
                   if (strpos($string, "productionyes") === false) {
                       if (strpos($string, "productionno") === false) {
                           if (strpos($string, "generalyes") === false) {
                               if (strpos($string, "generalno") === false) {
                               } else {

                                   $p_id = str_replace("generalno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {//$vasql

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' ");


                                       } else {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                       }

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                   }
                               }

                           } else {

                               $p_id = str_replace("generalyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {

                           $p_id = str_replace("productionno", "", $string);
                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];

                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' ");


                               } else {

                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                               }


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }

                           }
                       }

                   } else {

                       $p_id = str_replace("productionyes", "", $string);

                       //
                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                       $column = $row[0]['columns'];
                       if ($_POST['gender'] == "all") {

                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                           if ($_POST['va'] == "all") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' ");

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' ");

                           } else {//.$vasql

                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                           }


                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = 100;
                           } else {
                               $female = 100;
                           }


                       }
                   }


               }

               //echo $female;
               draw_pie_chart($male, $female, $json_model_obj, $util_obj);


           } else

               ///////////////////////////////////////////subcounties
               if ($_POST['country'] == "all") {

                   $table = "dataset_" . $_POST['id'];
                   $district = $_POST['district'];
                   $rows = array();
                   if ($_POST['production'] == "all") {

                       if ($_POST['gender'] == "all") {

                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                           if ($_POST['va'] == "all") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");
                               $total_farmers = $female + $male;

                           } else {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male'" . $vasql);

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female'" . $vasql);


                           }//.$vasql


                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");

                           } else {
                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");

                           }
                       }

                   }
                   else {
                       $string = $_POST['production'];
                       if (strpos($string, "productionyes") === false) {
                           if (strpos($string, "productionno") === false) {
                               if (strpos($string, "generalyes") === false) {
                                   if (strpos($string, "generalno") === false) {

                                   } else {
                                       $p_id = str_replace("generalno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }


                                       }

                                   }
                               } else {
                                   $p_id = str_replace("generalyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }
                               }


                           } else {
                               $p_id = str_replace("productionno", "", $string);

                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {
                           $p_id = str_replace("productionyes", "", $string);

                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];
                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                               } else {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                               }//.$vasql


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }


                           }

                       }


                   }

                   draw_pie_chart($male, $female, $json_model_obj, $util_obj);

               } else

                   ///////////////////////////////////////////parish

                   if ($_POST['parish'] == "all") {

                       $table = "dataset_" . $_POST['id'];
                       $subcounty = $_POST['country'];
                       $rows = array();
                       if ($_POST['production'] == "all") {
                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");

                               } else {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' " . $vasql);

                               }

                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");
                               } else {
                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");
                               }

                           }
                       } else {
                           $string = $_POST['production'];
                           if (strpos($string, "productionyes") === false) {
                               if (strpos($string, "productionno") === false) {
                                   if (strpos($string, "generalyes") === false) {
                                       if (strpos($string, "generalno") === false) {
                                       } else {
                                           $p_id = str_replace("generalno", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {


                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }


                                           }
                                       }


                                   } else {
                                       $p_id = str_replace("generalyes", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }

                               }
                           } else {
                               $p_id = str_replace("productionyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }

                               }


                           }
                       }

                       draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                   } else

                       ///////////////////////////////////////////village

                       if ($_POST['village'] == "all") {

                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");


                                   } else {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' " . $vasql);

                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' AND sacco_branch_name LIKE '$branch' ");
                                   } else {
                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' AND sacco_branch_name LIKE '$branch' ");
                                   }

                               }
                           } else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                                   } else {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }


                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }

                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }

                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }


                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                       }
                       else
                       {

                           //echo "off";
                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $village = $_POST['village'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];

                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           } else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'");

                                                   } else {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }

                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {

                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql

                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }
                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                           } else {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql

                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }

                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                       }

       }
       else {
           $util_obj->deliver_response(200, 0, null);
       }
   }
   else {
       if (isset($_POST['id']) && isset($_POST['district']) && isset($_POST['country']) && isset($_POST['parish']) && isset($_POST['village']))
       {
           $district = $_POST['district'];
           $subcounty = $_POST['country'];
           $parish = $_POST['parish'];
           $id = $_POST['id'];

           ///////////////////////////////////////districts
           if ($_POST['district'] == "all") {
               $table = "dataset_" . $_POST['id'];
               $rows = array();

               $male = 0;
               $female = 0;
               if ($_POST['production'] == "all") {

                   if ($_POST['gender'] == "all") {

                           $female = (int)$mCrudFunctions->get_count($table, " lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");
                           $male = (int)$mCrudFunctions->get_count($table, " lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                           $total_farmers = $female + $male;

                   } else {

                       $gender = $_POST['gender'];

                       if ($gender == "male") {
                           $male = (int)$mCrudFunctions->get_count($table, " lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");

                       } else {
                           $female = (int)$mCrudFunctions->get_count($table, " lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");

                       }
                   }

               }
               else {
                   $string = $_POST['production'];
                   if (strpos($string, "productionyes") === false) {
                       if (strpos($string, "productionno") === false) {
                           if (strpos($string, "generalyes") === false) {
                               if (strpos($string, "generalno") === false) {
                               } else {

                                   $p_id = str_replace("generalno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {//$vasql

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' ");


                                       } else {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                       }

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                   }
                               }

                           } else {

                               $p_id = str_replace("generalyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {

                           $p_id = str_replace("productionno", "", $string);
                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];

                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' ");


                               } else {

                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                               }


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }

                           }
                       }

                   } else {

                       $p_id = str_replace("productionyes", "", $string);

                       //
                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                       $column = $row[0]['columns'];
                       if ($_POST['gender'] == "all") {

                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                           if ($_POST['va'] == "all") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' ");

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' ");

                           } else {//.$vasql

                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                           }


                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = 100;
                           } else {
                               $female = 100;
                           }


                       }
                   }


               }

               //echo $female;
               draw_pie_chart($male, $female, $json_model_obj, $util_obj);


           } else

               ///////////////////////////////////////////subcounties
               if ($_POST['country'] == "all") {

                   $table = "dataset_" . $_POST['id'];
                   $district = $_POST['district'];
                   $rows = array();
                   if ($_POST['production'] == "all") {

                       if ($_POST['gender'] == "all") {

                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");
                               $total_farmers = $female + $male;

                       } else {
                           $gender = $_POST['gender'];
                           if ($gender == "male") {
                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");

                           } else {
                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");

                           }
                       }

                   }
                   else {
                       $string = $_POST['production'];
                       if (strpos($string, "productionyes") === false) {
                           if (strpos($string, "productionno") === false) {
                               if (strpos($string, "generalyes") === false) {
                                   if (strpos($string, "generalno") === false) {

                                   } else {
                                       $p_id = str_replace("generalno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }


                                       }

                                   }
                               } else {
                                   $p_id = str_replace("generalyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }
                               }


                           } else {
                               $p_id = str_replace("productionno", "", $string);

                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }

                       } else {
                           $p_id = str_replace("productionyes", "", $string);

                           //
                           $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                           $column = $row[0]['columns'];
                           if ($_POST['gender'] == "all") {

                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                               if ($_POST['va'] == "all") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                               } else {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                               }//.$vasql


                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = 100;
                               } else {
                                   $female = 100;
                               }


                           }

                       }


                   }

                   draw_pie_chart($male, $female, $json_model_obj, $util_obj);

               } else

                   ///////////////////////////////////////////parish

                   if ($_POST['parish'] == "all") {

                       $table = "dataset_" . $_POST['id'];
                       $subcounty = $_POST['country'];
                       $rows = array();
                       if ($_POST['production'] == "all") {
                           if ($_POST['gender'] == "all") {

                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");

                           } else {
                               $gender = $_POST['gender'];
                               if ($gender == "male") {
                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                               } else {
                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");
                               }
                           }
                       } else {
                           $string = $_POST['production'];
                           if (strpos($string, "productionyes") === false) {
                               if (strpos($string, "productionno") === false) {
                                   if (strpos($string, "generalyes") === false) {
                                       if (strpos($string, "generalno") === false) {
                                       } else {
                                           $p_id = str_replace("generalno", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {


                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }


                                           }
                                       }


                                   } else {
                                       $p_id = str_replace("generalyes", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionno", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql


                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }


                                   }

                               }
                           } else {
                               $p_id = str_replace("productionyes", "", $string);
                               //
                               $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                               $column = $row[0]['columns'];
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }

                               }


                           }
                       }

                       draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                   } else

                       ///////////////////////////////////////////village

                       if ($_POST['village'] == "all") {

                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");

                               } else {
                                   $gender = $_POST['gender'];
                                   if ($gender == "male") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'male' AND biodata_cooperative_name LIKE '$branch' ");
                                   } else {
                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_gender) = 'female' AND biodata_cooperative_name LIKE '$branch' ");
                                   }

                               }
                           } else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                                   } else {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }


                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql


                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }

                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                           } else {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql


                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }

                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {


                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }
                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }


                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                       }
                       else
                       {

                           //echo "off";
                           $table = "dataset_" . $_POST['id'];
                           $parish = $_POST['parish'];
                           $village = $_POST['village'];
                           $rows = array();
                           if ($_POST['production'] == "all") {
                               if ($_POST['gender'] == "all") {

                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                   if ($_POST['va'] == "all") {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                   } else {
                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                   }//.$vasql


                               } else {
                                   $gender = $_POST['gender'];

                                   if ($gender == "male") {
                                       $male = 100;
                                   } else {
                                       $female = 100;
                                   }


                               }
                           }
                           else {
                               $string = $_POST['production'];
                               if (strpos($string, "productionyes") === false) {
                                   if (strpos($string, "productionno") === false) {
                                       if (strpos($string, "generalyes") === false) {
                                           if (strpos($string, "generalno") === false) {
                                           } else {
                                               $p_id = str_replace("generalno", "", $string);
                                               //
                                               $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                               $column = $row[0]['columns'];
                                               if ($_POST['gender'] == "all") {

                                                   //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                   if ($_POST['va'] == "all") {

                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'");

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'");

                                                   } else {
                                                       $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                                       $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                                   }//.$vasql


                                               } else {
                                                   $gender = $_POST['gender'];
                                                   if ($gender == "male") {
                                                       $male = 100;
                                                   } else {
                                                       $female = 100;
                                                   }

                                               }

                                           }
                                       } else {
                                           $p_id = str_replace("generalyes", "", $string);
                                           //
                                           $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                           $column = $row[0]['columns'];
                                           if ($_POST['gender'] == "all") {

                                               //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                               if ($_POST['va'] == "all") {
                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                               } else {

                                                   $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                   $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                               }//.$vasql

                                           } else {
                                               $gender = $_POST['gender'];
                                               if ($gender == "male") {
                                                   $male = 100;
                                               } else {
                                                   $female = 100;
                                               }

                                           }

                                       }
                                   } else {
                                       $p_id = str_replace("productionno", "", $string);
                                       //
                                       $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                       $column = $row[0]['columns'];
                                       if ($_POST['gender'] == "all") {

                                           //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                           if ($_POST['va'] == "all") {

                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                           } else {
                                               $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                               $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                           }//.$vasql

                                       } else {
                                           $gender = $_POST['gender'];
                                           if ($gender == "male") {
                                               $male = 100;
                                           } else {
                                               $female = 100;
                                           }

                                       }

                                   }
                               } else {
                                   $p_id = str_replace("productionyes", "", $string);
                                   //
                                   $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                   $column = $row[0]['columns'];
                                   if ($_POST['gender'] == "all") {

                                       //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                       if ($_POST['va'] == "all") {

                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                       } else {
                                           $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                           $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                       }//.$vasql

                                   } else {
                                       $gender = $_POST['gender'];

                                       if ($gender == "male") {
                                           $male = 100;
                                       } else {
                                           $female = 100;
                                       }

                                   }

                               }
                           }

                           draw_pie_chart($male, $female, $json_model_obj, $util_obj);

                       }
       }
       else {
           $util_obj->deliver_response(200, 0, null);
       }
   }
}
else {
    if (isset($_POST['id']) && isset($_POST['district']) && isset($_POST['country']) && isset($_POST['parish']) && isset($_POST['village']) && isset($_POST['production']))
    {

        $district = $_POST['district'];
        $subcounty = $_POST['country'];
        $parish = $_POST['parish'];
        $id = $_POST['id'];

///////////////////////////////////////districts
        if ($_POST['district'] == "all") {
            $table = "dataset_" . $_POST['id'];
            $rows = array();

            $male = 0;
            $female = 0;
            if ($_POST['production'] == "all") {

                if ($_POST['gender'] == "all") {

//lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                    if ($_POST['va'] == "all") {

                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_farmer_gender) = 'female' ");

                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_farmer_gender) = 'male'  ");
                        $total_farmers = $female + $male;
                        if($total_farmers < 1){
                            if($_SESSION['account_name'] == 'Ankole Coffee Producers Cooperative Union Ltd'){ $total_farmers = (int)$mCrudFunctions->get_count("soil_results_".$id." s INNER JOIN dataset_".$id." d ON s.unique_id = d.unique_id", "1"); }
                            $total_farmers = (int)$mCrudFunctions->get_count($table, "1");
                        }

                    } else {
                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "   lower(biodata_farmer_gender) = 'female' " . $vasql);

                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  lower(biodata_farmer_gender) = 'male'   " . $vasql);
                    }

                } else {

                    $gender = $_POST['gender'];

                    if ($gender == "male") {
                        $male = 100;
                    } else {
                        $female = 100;
                    }
                }

            } else {
                $string = $_POST['production'];
                if (strpos($string, "productionyes") === false) {
                    if (strpos($string, "productionno") === false) {
                        if (strpos($string, "generalyes") === false) {
                            if (strpos($string, "generalno") === false) {
                            } else {

                                $p_id = str_replace("generalno", "", $string);
                                //
                                $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                $column = $row[0]['columns'];
                                if ($_POST['gender'] == "all") {


                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {//$vasql

                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' ");

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' ");


                                    } else {

                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  lower(biodata_farmer_gender) ='male' " . $vasql);

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                    }

                                } else {
                                    $gender = $_POST['gender'];

                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }
                                }
                            }

                        } else {

                            $p_id = str_replace("generalyes", "", $string);
                            //
                            $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                            $column = $row[0]['columns'];
                            if ($_POST['gender'] == "all") {

                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                if ($_POST['va'] == "all") {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' ");

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' ");


                                } else {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='male' " . $vasql);

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) ='female' " . $vasql);


                                }//.$vasql


                            } else {
                                $gender = $_POST['gender'];
                                if ($gender == "male") {
                                    $male = 100;
                                } else {
                                    $female = 100;
                                }


                            }
                        }

                    } else {

                        $p_id = str_replace("productionno", "", $string);
                        //
                        $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                        $column = $row[0]['columns'];

                        if ($_POST['gender'] == "all") {

                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                            if ($_POST['va'] == "all") {
                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' ");

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' ");


                            } else {

                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no'  AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                            }


                        } else {
                            $gender = $_POST['gender'];
                            if ($gender == "male") {
                                $male = 100;
                            } else {
                                $female = 100;
                            }

                        }
                    }

                } else {

                    $p_id = str_replace("productionyes", "", $string);

                    //
                    $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                    $column = $row[0]['columns'];
                    if ($_POST['gender'] == "all") {

                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                        if ($_POST['va'] == "all") {
                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' ");

                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' ");

                        } else {//.$vasql

                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                        }


                    } else {
                        $gender = $_POST['gender'];
                        if ($gender == "male") {
                            $male = 100;
                        } else {
                            $female = 100;
                        }


                    }
                }


            }

//echo $female;
            draw_pie_chart($male, $female, $json_model_obj, $util_obj);


        } else


///////////////////////////////////////////subcounties
            if ($_POST['country'] == "all") {

                $table = "dataset_" . $_POST['id'];
                $district = $_POST['district'];
                $rows = array();
                if ($_POST['production'] == "all") {

                    if ($_POST['gender'] == "all") {

                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                        if ($_POST['va'] == "all") {
                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male'");

                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female'");
                            $total_farmers = $female + $male;
                            if($total_farmers < 1){
                                if($_SESSION['account_name'] == 'Ankole Coffee Producers Cooperative Union Ltd'){ $total_farmers = (int)$mCrudFunctions->get_count("soil_results_".$id." s INNER JOIN dataset_".$id." d ON s.unique_id = d.unique_id", "1"); }
                                $total_farmers = (int)$mCrudFunctions->get_count($table, "1");
                            }

                        } else {
                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                        }//.$vasql


                    } else {
                        $gender = $_POST['gender'];
                        if ($gender == "male") {
                            $male = 100;
                        } else {
                            $female = 100;
                        }


                    }


                } else {
                    $string = $_POST['production'];
                    if (strpos($string, "productionyes") === false) {
                        if (strpos($string, "productionno") === false) {
                            if (strpos($string, "generalyes") === false) {
                                if (strpos($string, "generalno") === false) {

                                } else {
                                    $p_id = str_replace("generalno", "", $string);
                                    //
                                    $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                    $column = $row[0]['columns'];
                                    if ($_POST['gender'] == "all") {

                                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                        if ($_POST['va'] == "all") {
                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                        } else {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                        }//.$vasql


                                    } else {
                                        $gender = $_POST['gender'];
                                        if ($gender == "male") {
                                            $male = 100;
                                        } else {
                                            $female = 100;
                                        }


                                    }

                                }
                            } else {
                                $p_id = str_replace("generalyes", "", $string);
                                //
                                $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                $column = $row[0]['columns'];
                                if ($_POST['gender'] == "all") {

                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                    } else {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                    }//.$vasql


                                } else {
                                    $gender = $_POST['gender'];
                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }


                                }
                            }


                        } else {
                            $p_id = str_replace("productionno", "", $string);

                            //
                            $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                            $column = $row[0]['columns'];
                            if ($_POST['gender'] == "all") {

                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                if ($_POST['va'] == "all") {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                                } else {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                }//.$vasql


                            } else {
                                $gender = $_POST['gender'];
                                if ($gender == "male") {
                                    $male = 100;
                                } else {
                                    $female = 100;
                                }


                            }
                        }

                    } else {
                        $p_id = str_replace("productionyes", "", $string);

                        //
                        $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                        $column = $row[0]['columns'];
                        if ($_POST['gender'] == "all") {

                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                            if ($_POST['va'] == "all") {
                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' ");

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' ");


                            } else {
                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                            }//.$vasql


                        } else {
                            $gender = $_POST['gender'];
                            if ($gender == "male") {
                                $male = 100;
                            } else {
                                $female = 100;
                            }


                        }

                    }


                }


                draw_pie_chart($male, $female, $json_model_obj, $util_obj);


            } else

///////////////////////////////////////////parish

                if ($_POST['parish'] == "all") {

                    $table = "dataset_" . $_POST['id'];
                    $subcounty = $_POST['country'];
                    $rows = array();
                    if ($_POST['production'] == "all") {
                        if ($_POST['gender'] == "all") {

                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                            if ($_POST['va'] == "all") {
                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                            } else {
                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                            }


                        } else {
                            $gender = $_POST['gender'];
                            if ($gender == "male") {
                                $male = 100;
                            } else {
                                $female = 100;
                            }

                        }
                    } else {
                        $string = $_POST['production'];
                        if (strpos($string, "productionyes") === false) {
                            if (strpos($string, "productionno") === false) {
                                if (strpos($string, "generalyes") === false) {
                                    if (strpos($string, "generalno") === false) {
                                    } else {
                                        $p_id = str_replace("generalno", "", $string);
                                        //
                                        $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                        $column = $row[0]['columns'];
                                        if ($_POST['gender'] == "all") {


                                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                            if ($_POST['va'] == "all") {
                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                            } else {
                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                            }//.$vasql


                                        } else {
                                            $gender = $_POST['gender'];
                                            if ($gender == "male") {
                                                $male = 100;
                                            } else {
                                                $female = 100;
                                            }


                                        }
                                    }


                                } else {
                                    $p_id = str_replace("generalyes", "", $string);
                                    //
                                    $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                    $column = $row[0]['columns'];
                                    if ($_POST['gender'] == "all") {

                                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                        if ($_POST['va'] == "all") {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'");

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'");


                                        } else {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                        }//.$vasql


                                    } else {
                                        $gender = $_POST['gender'];
                                        if ($gender == "male") {
                                            $male = 100;
                                        } else {
                                            $female = 100;
                                        }

                                    }

                                }
                            } else {
                                $p_id = str_replace("productionno", "", $string);
                                //
                                $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                $column = $row[0]['columns'];
                                if ($_POST['gender'] == "all") {

                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                    } else {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'no' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                    }//.$vasql


                                } else {
                                    $gender = $_POST['gender'];
                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }


                                }

                            }
                        } else {
                            $p_id = str_replace("productionyes", "", $string);
                            //
                            $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                            $column = $row[0]['columns'];
                            if ($_POST['gender'] == "all") {

                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                if ($_POST['va'] == "all") {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' ");

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' ");


                                } else {

                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                }//.$vasql


                            } else {
                                $gender = $_POST['gender'];
                                if ($gender == "male") {
                                    $male = 100;
                                } else {
                                    $female = 100;
                                }

                            }


                        }
                    }

                    draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                } else

///////////////////////////////////////////village

                    if ($_POST['village'] == "all") {

                        $table = "dataset_" . $_POST['id'];
                        $parish = $_POST['parish'];
                        $rows = array();
                        if ($_POST['production'] == "all") {
                            if ($_POST['gender'] == "all") {

                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                if ($_POST['va'] == "all") {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_farmer_gender) = 'male' ");

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_farmer_gender) = 'female' ");


                                } else {

                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish'  AND lower(biodata_farmer_gender) = 'female' " . $vasql);

                                }//.$vasql


                            } else {
                                $gender = $_POST['gender'];
                                if ($gender == "male") {
                                    $male = 100;
                                } else {
                                    $female = 100;
                                }

                            }
                        } else {
                            $string = $_POST['production'];
                            if (strpos($string, "productionyes") === false) {
                                if (strpos($string, "productionno") === false) {
                                    if (strpos($string, "generalyes") === false) {
                                        if (strpos($string, "generalno") === false) {
                                        } else {
                                            $p_id = str_replace("generalno", "", $string);
                                            //
                                            $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                            $column = $row[0]['columns'];
                                            if ($_POST['gender'] == "all") {

                                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                if ($_POST['va'] == "all") {
                                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                                } else {

                                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty'  AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                                }//.$vasql


                                            } else {
                                                $gender = $_POST['gender'];
                                                if ($gender == "male") {
                                                    $male = 100;
                                                } else {
                                                    $female = 100;
                                                }


                                            }

                                        }
                                    } else {
                                        $p_id = str_replace("generalyes", "", $string);
                                        //
                                        $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                        $column = $row[0]['columns'];
                                        if ($_POST['gender'] == "all") {

                                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                            if ($_POST['va'] == "all") {
                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                            } else {
                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                            }//.$vasql


                                        } else {
                                            $gender = $_POST['gender'];
                                            if ($gender == "male") {
                                                $male = 100;
                                            } else {
                                                $female = 100;
                                            }

                                        }

                                    }

                                } else {
                                    $p_id = str_replace("productionno", "", $string);
                                    //
                                    $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                    $column = $row[0]['columns'];
                                    if ($_POST['gender'] == "all") {

                                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                        if ($_POST['va'] == "all") {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");


                                        } else {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                        }//.$vasql


                                    } else {
                                        $gender = $_POST['gender'];
                                        if ($gender == "male") {
                                            $male = 100;
                                        } else {
                                            $female = 100;
                                        }

                                    }

                                }

                            } else {
                                $p_id = str_replace("productionyes", "", $string);
                                //
                                $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                $column = $row[0]['columns'];
                                if ($_POST['gender'] == "all") {


                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {

                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' ");

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' ");

                                    } else {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " " . $column . "  LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                    }//.$vasql

                                } else {
                                    $gender = $_POST['gender'];
                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }
                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }

                                }


                            }
                        }

                        draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                    }
                    else
                    {

//echo "off";
                        $table = "dataset_" . $_POST['id'];
                        $parish = $_POST['parish'];
                        $village = $_POST['village'];
                        $rows = array();
                        if ($_POST['production'] == "all") {
                            if ($_POST['gender'] == "all") {

                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                if ($_POST['va'] == "all") {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                } else {
                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . " TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                }//.$vasql


                            } else {
                                $gender = $_POST['gender'];

                                if ($gender == "male") {
                                    $male = 100;
                                } else {
                                    $female = 100;
                                }


                            }
                        } else {
                            $string = $_POST['production'];
                            if (strpos($string, "productionyes") === false) {
                                if (strpos($string, "productionno") === false) {
                                    if (strpos($string, "generalyes") === false) {
                                        if (strpos($string, "generalno") === false) {
                                        } else {
                                            $p_id = str_replace("generalno", "", $string);
                                            //
                                            $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                            $column = $row[0]['columns'];
                                            if ($_POST['gender'] == "all") {

                                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                                if ($_POST['va'] == "all") {

                                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'");

                                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'");

                                                } else {
                                                    $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male'" . $vasql);

                                                    $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female'" . $vasql);


                                                }//.$vasql


                                            } else {
                                                $gender = $_POST['gender'];
                                                if ($gender == "male") {
                                                    $male = 100;
                                                } else {
                                                    $female = 100;
                                                }

                                            }

                                        }
                                    } else {
                                        $p_id = str_replace("generalyes", "", $string);
                                        //
                                        $row = $mCrudFunctions->fetch_rows("general_questions", "columns", " id='$p_id' ");
                                        $column = $row[0]['columns'];
                                        if ($_POST['gender'] == "all") {

                                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                            if ($_POST['va'] == "all") {
                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");


                                            } else {

                                                $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);

                                                $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND  TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                            }//.$vasql

                                        } else {
                                            $gender = $_POST['gender'];
                                            if ($gender == "male") {
                                                $male = 100;
                                            } else {
                                                $female = 100;
                                            }

                                        }

                                    }
                                } else {
                                    $p_id = str_replace("productionno", "", $string);
                                    //
                                    $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                    $column = $row[0]['columns'];
                                    if ($_POST['gender'] == "all") {

                                        //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                        if ($_POST['va'] == "all") {

                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                        } else {
                                            $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                            $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'no' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                        }//.$vasql

                                    } else {
                                        $gender = $_POST['gender'];
                                        if ($gender == "male") {
                                            $male = 100;
                                        } else {
                                            $female = 100;
                                        }

                                    }

                                }
                            } else {
                                $p_id = str_replace("productionyes", "", $string);
                                //
                                $row = $mCrudFunctions->fetch_rows("production_data", "columns", " id='$p_id' ");
                                $column = $row[0]['columns'];
                                if ($_POST['gender'] == "all") {

                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {

                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' ");
                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' ");

                                    } else {
                                        $male = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'male' " . $vasql);
                                        $female = (double)$mCrudFunctions->get_count($table, $ageFilter . "  " . $column . " LIKE 'Yes' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_district) LIKE '$district' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_subcounty) LIKE '$subcounty' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM biodata_farmer_location_farmer_village) LIKE '$village' AND lower(biodata_farmer_gender) = 'female' " . $vasql);


                                    }//.$vasql

                                } else {
                                    $gender = $_POST['gender'];

                                    if ($gender == "male") {
                                        $male = 100;
                                    } else {
                                        $female = 100;
                                    }

                                }

                            }
                        }

                        draw_pie_chart($male, $female, $json_model_obj, $util_obj);


                    }

    }
    else {
        $util_obj->deliver_response(200, 0, null);
    }
}

function draw_pie_chart($male, $female, $json_model_obj, $util_obj)
{

    $titleArray = array('text' => 'Gender Analysis');

    $datax = array();

    $temp = array('Male', $male);
    array_push($datax, $temp);

    array_push($datax, array('Female', $female));
//    array_push($datax, array('Total Farmers',$total_farmers));

//array_push($datax,array('name'=>'Female','y'=>$per_female,'sliced'=>true,'selected'=>true));
    $dataArray = array('type' => 'pie', 'name' => 'Gender', 'data' => $datax);

    $data = $json_model_obj->get_piechart_graph_json($titleArray, $dataArray);//
    $util_obj->deliver_response(200, 1, $data);
}

?>

