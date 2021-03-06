<style type="text/css">
    th, thead {
        height: 2em !important;
        background: teal;
        color: #fff !important;
    }

</style>
<?php
error_reporting(0);

#includes
session_start();
require_once dirname(dirname(__FILE__)) . "/php_lib/user_functions/crud_functions_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/database_query_processor_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/utility_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/pagination_class.php";

$mCrudFunctions = new CrudFunctions();

if (isset($_POST['id']) && $_POST['id'] != "") {
    $class = 'table table-bordered table-hover';

    $branch = $_SESSION['user_account'];
    $role =  $_SESSION['role'];
    $page = !empty($_POST['page']) ? (int)$_POST['page'] : 1;

    $per_page = 16;
    $id = $_POST['id'];
    $dataset_get_name = $mCrudFunctions->fetch_rows("datasets_tb", "*", " id =$id ");
    $dt_st_name = $dataset_get_name[0]['dataset_name'];
    $total_count_default = $mCrudFunctions->get_count("dataset_" . $id, 1);

    $total_count = !empty($_POST['total_count']) ? (int)$_POST['total_count'] : $total_count_default;
    $pagination_obj = new Pagination($page, $per_page, $total_count);
    $offset = $pagination_obj->getOffset();
    $gender = $_POST['biodata_gender'];
    $va = $_POST['va'];
    $va = str_replace("(", "-", $va);
    $va = str_replace(")", "", $va);
    $strings = explode('-', $va);

    $va = strtolower($strings[1]); // outputs: india

    if($role == 1){
        if (isset($_POST['district']) && $_POST['district'] != "") {

            switch ($_POST['district']) {
                case  "all" :
                    if ($_POST['gender'] == "all") {
                        ///v2 code interview_particulars_va_code
                        if ($_POST['va'] == "all") {

                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Details</th></tr></thead>";
                            output($id, " 1 ORDER BY biodata_first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";

                        } else {
//                            echo "Dataset: $dt_st_name";
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Details</th></tr></thead>";
                            output($id, " lower(REPLACE(REPLACE(va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                            echo "</table>";
                        }

                    } else {        //gender else option
                        //
                        //echo $gender;

                        if ($_POST['va'] == "all") {
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                            output($id, " lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";
                        } else {
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                            output($id, " lower(REPLACE(REPLACE(va_code,' ',''),'.','')) = '$va' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";
                        }

                    }

/////////////////////////////////////////////////////////////////////district all ends
                    break;
                default:

                    if ($_POST['subcounty'] == "all") {
                        $district = $_POST['district'];

///////////////////////////////////////////////////////////////////// subcounty all starts

                        if ($_POST['gender'] == "all") {
                            //echo $per_page;
                            //$snt= filter_var($district, FILTER_SANITIZE_STRING);
                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'

                            if ($_POST['va'] == "all") {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' ORDER BY first_name  LIMIT $per_page OFFSET $offset");
                                echo "</table>";

                            } else {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset");
                                echo "</table>";
                            }

                        } else {

                            if ($_POST['va'] == "all") {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                echo "<table>" . output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ") . "</table>";
                                echo "</table>";
                            } else {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(gender) = '$gender' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                echo "</table>";
                            }

                        }

//////////////////////////////////////////////////////////////////// subcounty all ends
                    } else {
                        $subcounty = $_POST['subcounty'];
                        $district = $_POST['district'];
                        if ($_POST['parish'] == "all") {

                            ////////////////////////////////////////////////////////////////////// parish all starts

                            if ($_POST['gender'] == "all") {

                                if ($_POST['va'] == "all") {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                                } else {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty'  AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                }

                            } else {
                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
//                            echo "parish not all";

                                if ($_POST['va'] == "all") {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                } else {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND lower(gender) = '$gender' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                }

                            }

///////////////////////////////////////////////////////////////////////////////// parish all ends
                        } else {
                            $subcounty = $_POST['subcounty'];
                            $district = $_POST['district'];
                            $parish = $_POST['parish'];

                            if ($_POST['village'] == "all") {

                                if ($_POST['gender'] == "all") {

                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    }

                                } else {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(gender) = '$gender' AND parish LIKE '%$parish%' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                                        echo "</table>";

                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(gender) = '$gender' AND parish LIKE '%$parish%' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                                        echo "</table>";
                                    }

                                }
                                /////////////////////////////////////////////////////////////////////////////
                            } else {
                                $subcounty = $_POST['subcounty'];
                                $district = $_POST['district'];
                                $parish = $_POST['parish'];
                                $village = $_POST['village'];

                                if ($_POST['gender'] == "all") {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'

                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    }


                                } else {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(gender) = '$gender'  AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";

                                    }


                                }


                            }

                        }

                    }

                    break;
            }

        }
    }
    elseif($role == 2) {
        if (isset($_POST['district']) && $_POST['district'] != "") {

            switch ($_POST['district']) {
                case  "all" :
                    if ($_POST['gender'] == "all") {
                        ///v2 code interview_particulars_va_code
                        if ($_POST['va'] == "all") {

                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Details</th></tr></thead>";
                            output($id, " sacco_branch_name LIKE '$branch' ORDER BY biodata_first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";

                        } else {
//                            echo "Dataset: $dt_st_name";
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Details</th></tr></thead>";
                            output($id, " lower(REPLACE(REPLACE(va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                            echo "</table>";
                        }

                    } else {        //gender else option
                        //
                        //echo $gender;

                        if ($_POST['va'] == "all") {
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                            output($id, " lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";
                        } else {
                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                            output($id, " lower(REPLACE(REPLACE(va_code,' ',''),'.','')) = '$va' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";
                        }

                    }

/////////////////////////////////////////////////////////////////////district all ends
                    break;
                default:

                    if ($_POST['subcounty'] == "all") {
                        $district = $_POST['district'];

///////////////////////////////////////////////////////////////////// subcounty all starts

                        if ($_POST['gender'] == "all") {
                            //echo $per_page;
                            //$snt= filter_var($district, FILTER_SANITIZE_STRING);
                            //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'

                            if ($_POST['va'] == "all") {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' ORDER BY first_name  LIMIT $per_page OFFSET $offset");
                                echo "</table>";

                            } else {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset");
                                echo "</table>";
                            }

                        } else {

                            if ($_POST['va'] == "all") {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                echo "<table>" . output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ") . "</table>";
                                echo "</table>";
                            } else {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(gender) = '$gender' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                echo "</table>";
                            }

                        }

//////////////////////////////////////////////////////////////////// subcounty all ends
                    } else {
                        $subcounty = $_POST['subcounty'];
                        $district = $_POST['district'];
                        if ($_POST['parish'] == "all") {

                            ////////////////////////////////////////////////////////////////////// parish all starts

                            if ($_POST['gender'] == "all") {

                                if ($_POST['va'] == "all") {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                                } else {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty'  AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                }

                            } else {
                                //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
//                            echo "parish not all";

                                if ($_POST['va'] == "all") {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                } else {
                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND lower(gender) = '$gender' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";
                                }

                            }

///////////////////////////////////////////////////////////////////////////////// parish all ends
                        } else {
                            $subcounty = $_POST['subcounty'];
                            $district = $_POST['district'];
                            $parish = $_POST['parish'];

                            if ($_POST['village'] == "all") {

                                if ($_POST['gender'] == "all") {

                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    }

                                } else {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(gender) = '$gender' AND parish LIKE '%$parish%' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                                        echo "</table>";

                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(gender) = '$gender' AND parish LIKE '%$parish%' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                                        echo "</table>";
                                    }

                                }
                                /////////////////////////////////////////////////////////////////////////////
                            } else {
                                $subcounty = $_POST['subcounty'];
                                $district = $_POST['district'];
                                $parish = $_POST['parish'];
                                $village = $_POST['village'];

                                if ($_POST['gender'] == "all") {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'

                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    }


                                } else {
                                    //lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va'
                                    if ($_POST['va'] == "all") {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";
                                    } else {
                                        echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                        output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(gender) = '$gender'  AND lower(REPLACE(REPLACE(interview_particulars_va_code,' ',''),'.','')) = '$va' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                        echo "</table>";

                                    }


                                }


                            }

                        }

                    }

                    break;
            }
        }
    }
    else {
        if (isset($_POST['district']) && $_POST['district'] != "") {

            switch ($_POST['district']) {
                case  "all" :
                    if ($_POST['gender'] == "all") {

                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Details</th></tr></thead>";
                            output($id, " biodata_cooperative_name LIKE '$branch' ORDER BY biodata_first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";

                    } else {        //gender else option

                            echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                            output($id, " lower(biodata_gender) = '$gender' AND biodata_cooperative_name LIKE '$branch' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                            echo "</table>";

                    }

/////////////////////////////////////////////////////////////////////district all ends
                    break;
                default:

                    if ($_POST['subcounty'] == "all") {
                        $district = $_POST['district'];

///////////////////////////////////////////////////////////////////// subcounty all starts

                        if ($_POST['gender'] == "all") {

                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' ORDER BY first_name  LIMIT $per_page OFFSET $offset");
                                echo "</table>";

                        } else {
                                echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                echo "<table>" . output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ") . "</table>";
                                echo "</table>";

                        }

//////////////////////////////////////////////////////////////////// subcounty all ends
                    }
                    else {
                        $subcounty = $_POST['subcounty'];
                        $district = $_POST['district'];
                        if ($_POST['parish'] == "all") {

                            ////////////////////////////////////////////////////////////////////// parish all starts

                            if ($_POST['gender'] == "all") {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, "  TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND biodata_cooperative_name LIKE '$branch' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                            } else {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                            }

///////////////////////////////////////////////////////////////////////////////// parish all ends
                        } else {
                            $subcounty = $_POST['subcounty'];
                            $district = $_POST['district'];
                            $parish = $_POST['parish'];

                            if ($_POST['village'] == "all") {

                                if ($_POST['gender'] == "all") {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND biodata_cooperative_name LIKE '$branch' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                                } else {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND lower(gender) = '$gender' AND parish LIKE '%$parish%' AND biodata_cooperative_name LIKE '$branch' ORDER BY first_name LIMIT $per_page OFFSET $offset  ");
                                    echo "</table>";

                                }
                                /////////////////////////////////////////////////////////////////////////////
                            } else {
                                $subcounty = $_POST['subcounty'];
                                $district = $_POST['district'];
                                $parish = $_POST['parish'];
                                $village = $_POST['village'];

                                if ($_POST['gender'] == "all") {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND biodata_cooperative_name LIKE '$branch' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                                } else {

                                    echo "<table class='$class'><thead class='bg bg-success'><tr><th>#</th><th>Name</th> <th>Age</th><th>Gender</th><th>Contact</th><th>Cows owned</th><th>Lactating Cows</th><th>Last milk supply</th><th>Date of last supply</th><th>Details</th></tr></thead>";
                                    output($id, " TRIM(TRAILING '.' FROM district) LIKE '$district' AND TRIM(TRAILING '.' FROM subcounty) LIKE '$subcounty' AND  TRIM(TRAILING '.' FROM parish) LIKE '$parish' AND TRIM(TRAILING '.' FROM village) LIKE '$village' AND lower(gender) = '$gender' ORDER BY first_name LIMIT $per_page OFFSET $offset ");
                                    echo "</table>";

                                }
                            }

                        }
                    }
                    break;
            }
        }
    }
}

//generating table as output
function output($id, $where)
{
    $util_obj = new Utilties();
    $db = new DatabaseQueryProcessor();
    $mCrudFunctions = new CrudFunctions();

    $client_id = $_SESSION["client_id"];
    $role =  $_SESSION['role'];

    $dts = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id'");
//    echo $id;
//    $id_ = $util_obj->encrypt_decrypt("encrypt", $id);
    $dataset = $mCrudFunctions->fetch_rows("datasets_tb", "*", " id = $id ");
    $dataset_name = $dataset[0]["dataset_name"];
    $dataset_type = $dataset[0]["dataset_type"];

    $table = "dataset_" . $id;
    if($role == 1){
//        foreach ($dts as $dt){
            $dataset_ = $util_obj->encrypt_decrypt("encrypt", $id);

            $rows = $mCrudFunctions->fetch_rows( "dataset_" .$id, "*", $where);

            if ($dataset_type == "Farmer") {
                if (sizeof($rows) != 0) {

//                echo "<p hidden='hidden' id='check_contect'></p>";
                    $counter = 1;
                    //            echo "inside table ........";
                    foreach ($rows as $row) {

                        //                print_r($row);
                        $real_id = $row['id'];

                        $real_id = $util_obj->encrypt_decrypt("encrypt", $real_id);
                        $name = $row['biodata_first_name'];
                        //                echo $name;
                        $last_name = $row['biodata_last_name'];
                        $gender = $row['biodata_gender'];
                        $date = $row['biodata_age'];
                        $district = $row['biodata_farmer_location_farmer_district'];
                        $milk = '';
                        $last_trans_date = '';
                        $phone_number = $row['biodata_phonenumber'];
                        $cows = $row['number_of_cows'] + $row['number_of_bulls']+ $row['number_of_calves'];
                        $lactating_cows = $row['lactating_cows'];
                        $age = explode('/',$date);
                        $farmer_age = 2017 - $age[2];
                        //                echo  "<br>".$farmer_age;

                        //                print_r($name);
                        //                $gender = $util_obj->captalizeEachWord($util_obj->remove_apostrophes($row['gender']));
                        //                $dob = $util_obj->remove_apostrophes($row['age']);
                        ////                $picture = $util_obj->remove_apostrophes($row['farmer_image']);
                        //                $district = $util_obj->captalizeEachWord($util_obj->remove_apostrophes($row['district']));
                        //                $phone_number = $util_obj->remove_apostrophes($row['phonenumber']);
                        //
                        //                //$uuid = $util_obj->remove_apostrophes($row['meta_instanceID']);
                        //                $age_ = $util_obj->getAge($dob, "Africa/Nairobi");
                        //$name = strlen($name) <= 15 ? $name : substr($name, 0, 14) . "...";

                        ////////////////////////////////////////////////////////////////////////
                        //                if ($_SESSION['client_id'] == 1) {
                        echo "<tr>";
                        echo "
                            <td>$counter</td>
    
                      <td style='line-height:12pt; align:center'>$name $last_name</td>
                      <td style='line-height:12pt; align:center'>$farmer_age</td>
                      <td style='line-height:12pt; align:center'>$gender</td>
                      <td style='line-height:12pt; align:center'>$phone_number</td>
                      <td style='line-height:12pt; align:center'>$cows</td>
                      <td style='line-height:12pt; align:center'>$lactating_cows</td>
                     ";

                        echo " <td> <a class='btn btn-success' href=\"dairyDetails.php?s=$dataset_&token=$real_id&type=$dataset_type\" style=\"color:#FFFFFF; padding: 8px; margin: 0;\">View Details</a>
                           </td>";
                        echo "</tr>";
                        $counter++;
                    }
                }
            }

//        }
    }
    elseif($role == 2) {
        foreach ($dts as $dt){
            $dataset_ = $util_obj->encrypt_decrypt("encrypt", $dt['id']);

            $rows = $mCrudFunctions->fetch_rows( "dataset_" .$dt['id'], "*", $where);

            if ($dataset_type == "Farmer") {
                if (sizeof($rows) != 0) {

//                echo "<p hidden='hidden' id='check_contect'></p>";
                    $counter = 1;
                    //            echo "inside table ........";
                    foreach ($rows as $row) {

                        //                print_r($row);
                        $real_id = $row['id'];

                        $real_id = $util_obj->encrypt_decrypt("encrypt", $real_id);
                        $name = $row['biodata_first_name'];
                        //                echo $name;
                        $last_name = $row['biodata_last_name'];
                        $gender = $row['biodata_gender'];
                        $date = $row['biodata_age'];
                        $district = $row['biodata_farmer_location_farmer_district'];
                        $milk = '';
                        $last_trans_date = '';
                        $phone_number = $row['biodata_phonenumber'];
                        $cows = $row['number_of_cows'] + $row['number_of_bulls']+ $row['number_of_calves'];
                        $lactating_cows = $row['lactating_cows'];
                        $age = explode('/',$date);
                        $farmer_age = 2017 - $age[2];
                        //                echo  "<br>".$farmer_age;

                        //                print_r($name);
                        //                $gender = $util_obj->captalizeEachWord($util_obj->remove_apostrophes($row['gender']));
                        //                $dob = $util_obj->remove_apostrophes($row['age']);
                        ////                $picture = $util_obj->remove_apostrophes($row['farmer_image']);
                        //                $district = $util_obj->captalizeEachWord($util_obj->remove_apostrophes($row['district']));
                        //                $phone_number = $util_obj->remove_apostrophes($row['phonenumber']);
                        //
                        //                //$uuid = $util_obj->remove_apostrophes($row['meta_instanceID']);
                        //                $age_ = $util_obj->getAge($dob, "Africa/Nairobi");
                        //$name = strlen($name) <= 15 ? $name : substr($name, 0, 14) . "...";

                        ////////////////////////////////////////////////////////////////////////
                        //                if ($_SESSION['client_id'] == 1) {
                        echo "<tr>";
                        echo "
                            <td>$counter</td>
    
                      <td style='line-height:12pt; align:center'>$name $last_name</td>
                      <td style='line-height:12pt; align:center'>$farmer_age</td>
                      <td style='line-height:12pt; align:center'>$gender</td>
                      <td style='line-height:12pt; align:center'>$phone_number</td>
                      <td style='line-height:12pt; align:center'>$cows</td>
                      <td style='line-height:12pt; align:center'>$lactating_cows</td>
                     ";

                        echo " <td> <a class='btn btn-success' href=\"dairyDetails.php?s=$dataset_&token=$real_id&type=$dataset_type\" style=\"color:#FFFFFF; padding: 8px; margin: 0;\">View Details</a>
                           </td>";
                        echo "</tr>";
                        $counter++;
                    }
                }
            }

        }
    }
    else {
        foreach ($dts as $dt){
            $dataset_ = $util_obj->encrypt_decrypt("encrypt", $dt['id']);

            $rows = $mCrudFunctions->fetch_rows( "dataset_" .$dt['id'], "*", $where);

            if ($dataset_type == "Farmer") {
                if (sizeof($rows) != 0) {

//                echo "<p hidden='hidden' id='check_contect'></p>";
                    $counter = 1;
                    //            echo "inside table ........";
                    foreach ($rows as $row) {

                        //                print_r($row);
                        $real_id = $row['id'];

                        $real_id = $util_obj->encrypt_decrypt("encrypt", $real_id);
                        $name = $row['biodata_first_name'];
                        //                echo $name;
                        $last_name = $row['biodata_last_name'];
                        $gender = $row['biodata_gender'];
                        $date = $row['biodata_age'];
                        $district = $row['biodata_farmer_location_farmer_district'];
                        $milk = '';
                        $last_trans_date = '';
                        $phone_number = $row['biodata_phonenumber'];
                        $cows = $row['number_of_cows'] + $row['number_of_bulls']+ $row['number_of_calves'];
                        $lactating_cows = $row['lactating_cows'];
                        $age = explode('/',$date);
                        $farmer_age = 2017 - $age[2];

                        ////////////////////////////////////////////////////////////////////////
                        //                if ($_SESSION['client_id'] == 1) {
                        echo "<tr>";
                        echo "
                            <td>$counter</td>
    
                      <td style='line-height:12pt; align:center'>$name $last_name</td>
                      <td style='line-height:12pt; align:center'>$farmer_age</td>
                      <td style='line-height:12pt; align:center'>$gender</td>
                      <td style='line-height:12pt; align:center'>$phone_number</td>
                      <td style='line-height:12pt; align:center'>$cows</td>
                      <td style='line-height:12pt; align:center'>$lactating_cows</td>
                     ";

                        echo " <td> <a class='btn btn-success' href=\"dairyDetails.php?s=$dataset_&token=$real_id&type=$dataset_type\" style=\"color:#FFFFFF; padding: 8px; margin: 0;\">View Details</a>
                           </td>";
                        echo "</tr>";
                        $counter++;
                    }
                }
            }

        }
    }
}

?>
