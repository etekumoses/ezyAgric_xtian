<?php
#includes
require_once dirname(dirname(__FILE__)) . "/php_lib/user_functions/json_models_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/user_functions/crud_functions_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/database_query_processor_class.php";
require_once dirname(dirname(__FILE__)) . "/php_lib/lib_functions/utility_class.php";

$mCrudFunctions = new CrudFunctions();
$json_model_obj = new JSONModel();
$util_obj = new Utilties();

switch ($_POST["token"]) {
    case "get_todays_milk":
        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];

        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");
        $group = array();   $persons = array();
        $today = date("Y-m-d", strtotime('today'));
        $from = "2017-11-13";
        $milk_data = file_get_contents("https://mcash.ug/farmers/?query=milkdata&access_token=b31ff5eb07171e028e7af6920bbbccab0b43136e08af525fd2cd40333db2ab31&start_date=$from&end_date=$today");
        $milk_periodic_data = json_decode($milk_data);
        foreach ($rows as $row){
            if($mCrudFunctions->check_table_exists("dataset_".$row['id'])){
                $data_table = "dataset_".$row['id'];
                if($role == 1) {
                    $results = $mCrudFunctions->fetch_rows($data_table, "biodata_first_name,biodata_last_name,biodata_phonenumber", 1);
                    foreach ($results as $result) {
                        $name = $result['biodata_first_name'] . " " . $result['biodata_last_name'];
                        $person = array("name" => $name, "mobile" => $result['biodata_phonenumber']);
                        array_push($persons, $person);
                    }
                }
                elseif ($role == 2){
                    $results = $mCrudFunctions->fetch_rows($data_table, "biodata_first_name,biodata_last_name,biodata_phonenumber", "sacco_branch_name LIKE '$branch'");
                    foreach ($results as $result) {
                        $name = $result['biodata_first_name'] . " " . $result['biodata_last_name'];
                        $person = array("name" => $name, "mobile" => $result['biodata_phonenumber']);
                        array_push($persons, $person);
                    }
                }
                else {
                    $results = $mCrudFunctions->fetch_rows($data_table, "biodata_first_name,biodata_last_name,biodata_phonenumber", "biodata_cooperative_name LIKE '$branch'");
                    foreach ($results as $result) {
                        $name = $result['biodata_first_name'] . " " . $result['biodata_last_name'];
                        $person = array("name" => $name, "mobile" => $result['biodata_phonenumber']);
                        array_push($persons, $person);
                    }
                }
            }
        }

        foreach ($milk_periodic_data as $dairy_data){
            $milk_quantity = 0;
            $account = $dairy_data->account_no;
            $mobile_no = substr($account, 6);
             $milk = $dairy_data->milk_amount;
             $date = new DateTime($dairy_data->created_at);
             $date = $date->format("Y-m-d");
            if($role == 1){
                foreach ($persons as $farmer){
                    $farmer_name = $farmer['name'];
                    $farmer_mobile = $farmer['mobile'];
                    if($farmer_mobile == $mobile_no){
                        $milk_quantity += $milk;
                        $farmer_data = array('name' => $farmer_name, 'milk' => $milk_quantity, 'phone_no' => $farmer_mobile, 'date' => $date);
                        array_push($group, $farmer_data);
                    }
                }
            }
            elseif ($role == 2){
                foreach ($persons as $farmer){
                    $farmer_name = $farmer['name'];
                    $farmer_mobile = $farmer['mobile'];
                    if($farmer_mobile == $mobile_no){
                        $milk_quantity += $milk;
                        $farmer_data = array('name' => $farmer_name, 'milk' => $milk_quantity, 'phone_no' => $farmer_mobile, 'date' => $date);
                        array_push($group, $farmer_data);
                    }
                }
            }
            else {
                foreach ($persons as $farmer){
                    $farmer_name = $farmer['name'];
                    $farmer_mobile = $farmer['mobile'];
                    if($farmer_mobile == $mobile_no){
                        $milk_quantity += $milk;
                        $farmer_data = array('name' => $farmer_name, 'milk' => $milk_quantity, 'phone_no' => $farmer_mobile, 'date' => $date);
                        array_push($group, $farmer_data);
                    }
                }
            }
        }

        echo "
              <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-hover table-bordered\" id='dairyTable'>
                <thead >
                    <tr>
                        <td><input type=\"text\" id=\"0\" class=\"transactions-search-input\"></td>
                        <td><input type=\"text\" id=\"1\" class=\"transactions-search-input\"></td>
                        <td><input type=\"date\" id=\"2\" class=\"transactions-search-input\" placeholder=\"Date\"></td>
                 <!--      <td><input type=\"date\" id=\"3\" class=\"transactions-search-input\" placeholder=\"Date To\"></td> -->
                        <td><input type=\"text\" id=\"3\" class=\"transactions-search-input\"</td>
                    </tr>
                    <tr><th>Farmer</th><th>Contact</th><th>Date</th><th>Quantity(Ltrs)</th></tr>
                </thead>";
            foreach ($group as $element){
                $name = $element['name'];
                $milk = $element['milk'];
                $mobile_no = $element['phone_no'];
                $date = $element['date'];
                echo "<tr><td>$name</td><td>0$mobile_no</td><td>$date</td><td>$milk</td></tr>";
            }

        echo "</table>";

        break;

    case  "get_expenditure":
        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $labor_expenditure = 0; $injection_expenditure = 0; $acariceides_expenditure = 0;
        $tools_expenditure = 0; $deworming_expenditure = 0; $clearing_expenditure = 0;

        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                $data_table = "dataset_" . $row['id'];
                if($role == 1){
                    $labor_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_labor", 1);
                    $injection_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_injections", 1);
                    $acariceides_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_acaricides", 1);
                    $deworming_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_deworning", 1);
                    $clearing_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_land_clearing", 1);
                    $tools_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_farm_tools", 1);
                }
                if($role == 2){
                    $labor_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_labor", "sacco_branch_name LIKE '$branch'");
                    $injection_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_injections", "sacco_branch_name LIKE '$branch'");
                    $acariceides_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_acaricides", "sacco_branch_name LIKE '$branch'");
                    $deworming_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_deworning", "sacco_branch_name LIKE '$branch'");
                    $clearing_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_land_clearing", "sacco_branch_name LIKE '$branch'");
                    $tools_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_farm_tools", "sacco_branch_name LIKE '$branch'");
                }
                else {
                    $labor_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_labor", "biodata_cooperative_name LIKE '$branch'");
                    $injection_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_injections", "biodata_cooperative_name LIKE '$branch'");
                    $acariceides_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_acaricides", "biodata_cooperative_name LIKE '$branch'");
                    $deworming_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_deworning", "biodata_cooperative_name LIKE '$branch'");
                    $clearing_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_land_clearing", "biodata_cooperative_name LIKE '$branch'");
                    $tools_expenditure += (int)$mCrudFunctions->get_sum("$data_table", "expenditure_on_farm_tools", "biodata_cooperative_name LIKE '$branch'");
                }
            }
        }
        echo "
            <table class='table table-responsive table-bordered' style='height: 85%; font-size: larger;'>
                <thead class='dark' style=\" min-height: 20%; font-weight: bolder; font-size: 1.2em; \">
                <td colspan=\"2\" class='text-center'>Expenditure Break Down </td>
                
                </thead>

                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Labour Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($labor_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Injections Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($injection_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Acaricides Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($acariceides_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Deworming Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($deworming_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Clearing Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($clearing_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:dodgerblue\">Tools Expenses</td>
                    <td style=\" font-weight: bolder; color:dodgerblue\"> " . number_format($tools_expenditure) . " UGX</td>
                </tr>
                <tr>
                    <td style=\" font-weight: bolder; color:#000\">Total Expenditure</td>
                    <td style=\" font-weight: bolder; color:#000\"> " . number_format($tools_expenditure + $clearing_expenditure + $deworming_expenditure + $acariceides_expenditure + $injection_expenditure + $labor_expenditure) . " UGX</td>
                </tr>  
                
            </table>
        ";

        break;

    case  "farmers_piechart" :

        if (isset($_POST['gender'])) {
            session_start();
            $client_id = $_SESSION["client_id"];
            $role =  $_SESSION['role'];
            $branch = $_SESSION['user_account'];
            $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

            $male = 0;  $female = 0;

            foreach ($rows as $row) {
                if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                    if($role == 1){
                        $male += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'male'");
                        $female += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'female'");
                    } elseif($role == 2){
                        $male += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'male' AND sacco_branch_name LIKE '$branch' ");
                        $female += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'female' AND sacco_branch_name LIKE '$branch' ");
                    }else {
                        $male += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'male' AND biodata_cooperative_name LIKE '$branch' ");
                        $female += $mCrudFunctions->get_count("dataset_" . $row['id'], "lower(biodata_gender) LIKE 'female' AND biodata_cooperative_name LIKE '$branch' ");
                    }
                }
            }
            draw_donut_chart($male, $female);
        }
        break;

    case  "milk_collection" :
        session_start();
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $client_id = $_SESSION["client_id"];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $area = array(); $milk_supply = array();

        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                if($role == 1){
                    $milk_quantity = $mCrudFunctions->fetch_rows("dataset_".$row['id'], "DISTINCT(sacco_branch_name)as sacco, SUM(milk_production_data_milk_for_dairy) AS MILK_SUPPLIED", "1 GROUP BY sacco_branch_name ORDER BY sacco_branch_name ASC" );
                    foreach ($milk_quantity as $milk){
                        array_push($area, $milk['sacco']);
                        array_push($milk_supply, $milk['MILK_SUPPLIED']);
                    }
                }elseif($role == 2){
                    $milk_quantity = $mCrudFunctions->fetch_rows("dataset_".$row['id'], "DISTINCT(biodata_cooperative_name)as cooperative, SUM(milk_production_data_milk_for_dairy) AS MILK_SUPPLIED", "sacco_branch_name LIKE '$branch' GROUP BY cooperative ORDER BY cooperative ASC" );
                    foreach ($milk_quantity as $milk){
                        array_push($area, $milk['cooperative']);
                        array_push($milk_supply, $milk['MILK_SUPPLIED']);
                    }
                }else{
                    $milk_quantity = $mCrudFunctions->fetch_rows("dataset_".$row['id'], "DISTINCT(biodata_farmer_location_farmer_village)as village, SUM(milk_production_data_milk_for_dairy) AS MILK_SUPPLIED", "biodata_cooperative_name LIKE '$branch' GROUP BY village ORDER BY village ASC" );
                    foreach ($milk_quantity as $milk){
                        array_push($area, $milk['village']);
                        array_push($milk_supply, $milk['MILK_SUPPLIED']);
                    }
                }

            }
        }
        if($role == 1) milk_per_branch($area, $milk_supply, "column", "Quantity Of Milk Supplied Per Branch");
        elseif ($role == 2) milk_per_cooperative($area, $milk_supply, "column", "Quantity Of Milk Supplied Per Cooperative");
        else milk_per_village($area, $milk_supply, "column", "Quantity Of Milk Supplied Per Village");

        break;

    case  "farmers_districts" :
        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $district = array();    $no_farmers = array();  $youth = array(); $old = array();

        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                if($role == 1){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],
                        "sacco_branch_name AS sacco, COUNT(*) AS farmers, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) < 35 OR (2017-CONCAT(19,substring(biodata_age,-2,2))) = 35 THEN 1 ELSE 0 end) AS youth, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) > 35 THEN 1 ELSE 0 end) AS old ", "1 GROUP by sacco ORDER by sacco ASC ");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($district, $sel['sacco']);
                        array_push($youth, $sel['youth']);
                        array_push($old, $sel['old']);
                    }
                }elseif($role == 2){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],
                        "biodata_cooperative_name AS cooperative, COUNT(*) AS farmers, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) < 35 OR (2017-CONCAT(19,substring(biodata_age,-2,2))) = 35 THEN 1 ELSE 0 end) AS youth, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) > 35 THEN 1 ELSE 0 end) AS old ", "sacco_branch_name LIKE '$branch' GROUP by cooperative ORDER by cooperative ASC ");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($district, $sel['cooperative']);
                        array_push($youth, $sel['youth']);
                        array_push($old, $sel['old']);
                    }
                } else{
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],
                        "biodata_farmer_location_farmer_village AS village, COUNT(*) AS farmers, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) < 35 OR (2017-CONCAT(19,substring(biodata_age,-2,2))) = 35 THEN 1 ELSE 0 end) AS youth, 
                       SUM(CASE WHEN (2017-CONCAT(19,substring(biodata_age,-2,2))) > 35 THEN 1 ELSE 0 end) AS old ", "biodata_cooperative_name LIKE '$branch' GROUP by village ORDER by village ASC ");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($district, $sel['village']);
                        array_push($youth, $sel['youth']);
                        array_push($old, $sel['old']);
                    }
                }
            }
        }
        if($role == 1) draw_sacco_graph($district, $no_farmers, $youth, $old, "Number OF Farmers in Branches");
        elseif ($role == 2) draw_branch_graph($district, $no_farmers, $youth, $old, "Number OF Farmers in Cooperatives");
        else draw_cooperative_graph($district, $no_farmers, $youth, $old, "Number OF Farmers in Villages");
        break;

    case  "crops_grown" :
        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $amount = array(); $labour = 0;   $injection = 0; $land = 0; $animal_feeds = 0;
        $acaricides = 0;   $deworming = 0;  $clearing = 0;    $tools = 0; $fencing = 0;

        $labels = array('Acaricides','Labour','Injections','Tools','Deworming','Land','Feeds','Fencing');
        $expenses = array();
        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                if($role == 1){
                    $labour += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_labor", 1);
                    $injection += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_injections", 1);
                    $acaricides += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_acaricides", 1);
                    $land += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_land_clearing", 1);
                    $animal_feeds += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_animal_feeeds", 1);
                    $deworming += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_deworning", 1);
                    $tools += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_farm_tools", 1);
                    $fencing += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_fencing", 1);
                }
                elseif ($role == 2){
                    $labour += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_labor", "sacco_branch_name LIKE '$branch'");
                    $injection += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_injections", "sacco_branch_name LIKE '$branch'");
                    $acaricides += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_acaricides", "sacco_branch_name LIKE '$branch'");
                    $land += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_land_clearing", "sacco_branch_name LIKE '$branch'");
                    $animal_feeds += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_animal_feeeds", "sacco_branch_name LIKE '$branch'");
                    $deworming += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_deworning", "sacco_branch_name LIKE '$branch'");
                    $tools += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_farm_tools", "sacco_branch_name LIKE '$branch'");
                    $fencing += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_fencing", "sacco_branch_name LIKE '$branch'");
                }
                else {
                    $labour += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_labor", "biodata_cooperative_name LIKE '$branch'");
                    $injection += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_injections", "biodata_cooperative_name LIKE '$branch'");
                    $acaricides += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_acaricides", "biodata_cooperative_name LIKE '$branch'");
                    $land += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_land_clearing", "biodata_cooperative_name LIKE '$branch'");
                    $animal_feeds += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_animal_feeeds", "biodata_cooperative_name LIKE '$branch'");
                    $deworming += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_deworning", "biodata_cooperative_name LIKE '$branch'");
                    $tools += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_farm_tools", "biodata_cooperative_name LIKE '$branch'");
                    $fencing += $mCrudFunctions->get_sum("dataset_" . $row['id'], "expenditure_on_fencing", "biodata_cooperative_name LIKE '$branch'");
                }
            }
        }
//        if($role == 3){
            array_push($expenses, $acaricides);
            array_push($expenses, $labour);
            array_push($expenses, $injection);
            array_push($expenses, $tools);
            array_push($expenses, $deworming);
            array_push($expenses, $land);
            array_push($expenses, $animal_feeds);
            array_push($expenses, $fencing);
//        }
        farmer_expenditure($labels, $expenses, "column", "Farmers Expenditure");
        break;

    case "youth_farmers" :

        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $farmers_number = array();
        $tp = array();
        $youth = array();
        $old = array();

        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                if($role == 1){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],"DISTINCT(mode_of_transport) as transport, 
                    COUNT(*) as farmers", "1 GROUP BY mode_of_transport ORDER BY farmers DESC ");

                    foreach ($slected_rows as $sel) {
                        array_push($farmers_number, $sel['farmers']);
                        array_push($tp, $sel['transport']);
                    }
                } elseif ($role == 2){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],"DISTINCT(mode_of_transport) as transport, 
                    COUNT(*) as farmers","sacco_branch_name LIKE '$branch' GROUP BY mode_of_transport ORDER BY farmers DESC ");

                    foreach ($slected_rows as $sel) {
                        array_push($farmers_number, $sel['farmers']);
                        array_push($tp, $sel['transport']);
                    }
                } else {
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'],"DISTINCT(mode_of_transport) as transport, 
                           COUNT(*) as farmers", "biodata_cooperative_name LIKE '$branch' GROUP BY mode_of_transport ORDER BY farmers DESC ");

                    foreach ($slected_rows as $sel) {
                        array_push($farmers_number, $sel['farmers']);
                        array_push($tp, $sel['transport']);
                    }
                }
            }
        }
        analyseSeeds($tp, $farmers_number, "column", "Milk transportation modes used");
        break;

    case  "average_yield" :

        session_start();
        $client_id = $_SESSION["client_id"];
        $role =  $_SESSION['role'];
        $branch = $_SESSION['user_account'];
        $rows = $mCrudFunctions->fetch_rows("datasets_tb", "*", "client_id='$client_id' AND dataset_type='Farmer'");

        $breeds = array();
        $no_farmers = array();

        foreach ($rows as $row) {
            if ($mCrudFunctions->check_table_exists("dataset_" . $row['id'])) {
                if($role == 1){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'] . "  ",
                        "DISTINCT(cattle_breed) as breeds, COUNT(*) as farmers","1 GROUP BY breeds");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($breeds, $sel['breeds']);
                    }
                } elseif ($role == 2){
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'] . "  ",
                        "DISTINCT(cattle_breed) as breeds, COUNT(*) as farmers","sacco_branch_name LIKE '$branch' GROUP BY breeds");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($breeds, $sel['breeds']);
                    }
                } else{
                    $slected_rows = $mCrudFunctions->fetch_rows("dataset_" . $row['id'] . "  ",
                        "DISTINCT(cattle_breed) as breeds, COUNT(*) as farmers",
                        "biodata_cooperative_name LIKE '$branch' GROUP BY breeds");

                    foreach ($slected_rows as $sel) {
                        array_push($no_farmers, $sel['farmers']);
                        array_push($breeds, $sel['breeds']);
                    }
                }
            }
        }
        analyseSeeds($breeds, $no_farmers, "column", "Farmers with different breeds");
        break;
}


function debug_to_console($data)
{
    if (is_array($data) || is_object($data)) {
        echo("<script>console.log('PHP: " . json_encode($data) . "');</script>");
    } else {
        echo("<script>console.log('PHP: $data');</script>");
    }
}

function draw_donut_chart($male, $female)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    $titleArray = array('text' => 'Gender Analysis');

    $datax = array();

    $temp = array('Male', $male);
    array_push($datax, $temp);

    array_push($datax, array('Female', $female));

    $dataArray = array('type' => 'pie', 'name' => 'Gender', 'data' => $datax);

    $data = $json_model_obj->get_donutchart_graph_json($titleArray, $dataArray);//
    $util_obj->deliver_response(200, 1, $data);
}

function draw_graph($district, $no_farmers, $youth, $old, $type)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $no_farmers);
    $old_int = array_map(create_function('$value', 'return (int)$value;'), $old);
    $youth_int = array_map(create_function('$value', 'return (int)$value;'), $youth);

    $data = $json_model_obj->get_column_graph($district, $array_int, $youth_int, $old_int, $type);
    $util_obj->deliver_response(200, 1, $data);
}

function draw_sacco_graph($district, $no_farmers, $youth, $old, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $no_farmers);
    $old_int = array_map(create_function('$value', 'return (int)$value;'), $old);
    $youth_int = array_map(create_function('$value', 'return (int)$value;'), $youth);

    $data = $json_model_obj->get_column_graph($district, $array_int, $youth_int, $old_int, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function draw_branch_graph($district, $no_farmers, $youth, $old, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $no_farmers);
    $old_int = array_map(create_function('$value', 'return (int)$value;'), $old);
    $youth_int = array_map(create_function('$value', 'return (int)$value;'), $youth);

    $data = $json_model_obj->get_column_graph($district, $array_int, $youth_int, $old_int, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function draw_cooperative_graph($district, $no_farmers, $youth, $old, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $no_farmers);
    $old_int = array_map(create_function('$value', 'return (int)$value;'), $old);
    $youth_int = array_map(create_function('$value', 'return (int)$value;'), $youth);

    $data = $json_model_obj->get_column_graph($district, $array_int, $youth_int, $old_int, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function draw_climate_graph($district, $used_action, $no_action, $type){
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $used_action);
    $no_action_int = array_map(create_function('$value', 'return (int)$value;'), $no_action);

    $data = $json_model_obj->drawActionGraph($district, $array_int, $no_action_int, $type);
    $util_obj->deliver_response(200, 1, $data);
}

function analyseSeeds($source, $farmers, $type, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $farmers);


    $data = $json_model_obj->drawSeedGraph($source, $array_int, $type, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function farmer_expenditure($source, $farmers, $type, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $farmers);


    $data = $json_model_obj->drawFarmerExpenditureGraph($source, $array_int, $type, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function milk_per_village($source, $milk_supply, $type, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $milk_supply);


    $data = $json_model_obj->drawMilkPerVillageGraph($source, $array_int, $type, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function milk_per_branch($source, $milk_supply, $type, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $milk_supply);


    $data = $json_model_obj->drawMilkPerBranchGraph($source, $array_int, $type, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function milk_per_cooperative($source, $milk_supply, $type, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    //converting a string array into an array of integers
    $array_int = array_map(create_function('$value', 'return (int)$value;'), $milk_supply);


    $data = $json_model_obj->drawMilkPerCooperativeGraph($source, $array_int, $type, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function analyseRegions($type, $categories, $farmers, $males, $females, $title)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    $array_int = array_map(create_function('$value', 'return (int)$value;'), $farmers);
    $males_int = array_map(create_function('$value', 'return (int)$value;'), $males);
    $females_int = array_map(create_function('$value', 'return (int)$value;'), $females);

    $data = $json_model_obj->getRegions($type, $categories, $array_int, $males_int, $females_int, $title);
    $util_obj->deliver_response(200, 1, $data);
}

function cropsGrown($type)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();
    $data = $json_model_obj->getCropsGrown($type);
    $util_obj->deliver_response(200, 1, $data);
}

function connection()
{
    $mycon = new mysqli("host", "user", "password", "database");
    return $mycon;
}

function draw_milkhours_pie_chart($morning, $evening)
{
    $json_model_obj = new JSONModel();
    $util_obj = new Utilties();

    $titleArray = array('text' => 'Milk collection hours');

    $datax = array();

    $temp = array('Morning', $morning);
    array_push($datax, $temp);

    $femdata = array('Evening', $evening);
    array_push($datax, $femdata);

    $dataArray = array('type' => 'pie', 'name' => 'Ict', 'data' => $datax);

    $data = $json_model_obj->get_piechart_graph_json($titleArray, $dataArray);
    $util_obj->deliver_response(200, 1, $data);

}

?>