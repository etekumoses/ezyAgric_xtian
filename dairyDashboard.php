<?php include("include/header_client.php"); ?>
<style type="text/css">
    .content h2 {
        color: #ffffff;
        text-align: center;
        background: teal;
        margin-top: -.01em;
        width: 100%;
        font-size: 1em;
        border: 1px #eee solid;
        text-transform: capitalize;
        padding: 6px;
    }

    .content h4:nth-child(3) {
        color: #666;
        font-size: 1.2em;
        margin: 10px 0 !important;
        padding-left: 15px;
        font-weight: lighter;
    }

    .content h4:nth-child(2) {
        color: #666;
        font-size: 1.2em;
        margin: 10px 0 !important;
        padding-left: 15px;
        font-weight: lighter;
    }

    #dataset .thumbnail {
        height: 100px;
        width: 100%;
    }

    body.nav-md .container.body .right_col {
        width: 100%;
        padding-right: 0
    }

    .right_col {
        padding: 10px !important;
    }

    .navbar-right {
        margin-right: 0;
    }

    .tile {
        border-radius: 6px;
    }

    .x_panel {
        position: relative;
        width: 100%;
        margin-bottom: 10px;
        padding: 10px 17px;
        display: inline-block;
        background: #fff;
        border: 1px solid #E6E9ED;
        -webkit-column-break-inside: avoid;
        -moz-column-break-inside: avoid;
        column-break-inside: avoid;
        opacity: 1;
        -moz-transition: all .2s ease;
        -o-transition: all .2s ease;
        -webkit-transition: all .2s ease;
        -ms-transition: all .2s ease;
        transition: all .2s ease;
    }

    .x_title {
        border-bottom: 2px solid #E6E9ED;
        padding: 1px 5px 6px;
        margin-bottom: 10px;
    }

    .x_titles {
        border-bottom: 2px solid #E6E9ED;
        padding: 1px 5px 6px;

        margin-bottom: 10px;
        margin-left: 13px;
        width: 98%;
    }

    .x_title .filter {
        width: 40%;
        float: right;
    }

    .x_title h2 {
        margin: 5px 0 6px;
        float: left;
        display: block;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }

    .x_title h2 small {
        margin-left: 10px;
    }

    .x_title span {
        color: #BDBDBD;
    }

    .x_content {
        padding: 0 5px 6px;
        position: relative;
        width: 100%;
        float: left;
        clear: both;
        margin-top: 5px;
    }

    .x_content h4 {
        font-size: 16px;
        font-weight: 500;
    }

    legend {
        padding-bottom: 7px;
    }

    .modal-title {
        margin: 0;
        line-height: 1.42857143;
    }

    .demo-placeholder {
        height: 280px;
    }

    #dataset {
        background: #ffffff;
        padding-top: 15px;
    }

    #data_2 {
        margin-top: -4.5em;
    }

    .move_data {
        margin-left: 2em !important;
    }
</style>

<?php include "./include/breadcrumb.php" ?>

<div class="container-fluid">
    <div class="col-md-12 col-sm-4 col-xs-12">
        <div id="outgrower_dash" class="row" style='background-color: #F2F2F2; padding-top: 15px;'>


        </div>
    </div>
</div>


<?php
include("include/preloader.php");
//include("include/empty.php");

?>


<div class="container-fluid">
    <div class=" col-md-4" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
        <div id="farmers_regions" class="col-lg-12" style='height:300px; padding-top: 10px;'>

        </div>
    </div>

    <div class=" col-md-4 ">
        <div class="col-md-12" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
            <div id="gender_graph" class="col-lg-12 " style=" height:300px; padding-top: 10px;">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="col-md-12" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
            <div id="milk_collected" class="col-lg-12 " style=" height:300px; padding-top: 10px;">
            </div>
        </div>
    </div>
</div>
<br>
<div class="container-fluid">
    <div class=" col-md-4" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
        <div class="col-lg-12" id="crops_grown" style=" height:350px; padding-top: 10px;">

        </div>
    </div>
    <div class=" col-md-4" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
        <div class="col-lg-12" id="youth_farmers" style=" height:350px; padding-top: 10px;">

        </div>
    </div>
    <div class=" col-md-4" style="box-shadow: 1px 1px 1px 1px lightgray; background-color: rgba(182,184,182,0.16);">
        <div class="col-lg-12" id="average_yield" style=" height:350px; padding-top: 10px;">

        </div>
    </div>
</div>
<div>
    <br>
    <br>
</div>

<?php include("include/footer_client.php"); ?>

<!--piechart scripting-->

<script type="text/javascript">


    $(document).ready(function () {
        getLoadOutGrowerDash();
//        getLoadDataSets();
//        formGenderGraph();
        getAjaxData(1);
        ictUsageGraph();               //piechart for ict usage
//        farmersDistrictGraph();             //farmers column graph
        cropsAnalysis("name_crop");
        youthFarmers(" ");
        averageYield(' ');

        FormGenderGraph("all");
    });

    $("#search_holder").on("input", function (e) {
        getLoadDataSets();
    });

    function FormGenderGraph(gender) {

        token = "farmers_piechart";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token: token,
                gender: gender
            },
            success: function (data) {

                $('#gender_graph').highcharts(data,function(chart) { // on complete

                });
            }
        });
        hideProgressBar();
    }
    function ictUsageGraph() {

        token = "milk_collection";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token: token
//                ict_range: range
            },
            success: function (data) {

                $('#milk_collected').highcharts(data);
            }
        });
        hideProgressBar();
    }
    //-------------------------getting ajax data directly-- trial
    function getAjaxData(id) {
        token = "farmers_districts";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token: token,
                gender: id
            },
            success: function (data) {
                $('#farmers_regions').highcharts(data);
            }
        });

    }

    function cropsAnalysis(name) {

        token = "crops_grown";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token: token,
                farmers: "farmers",
                crop: name
            },
            success: function (data) {

                $('#crops_grown').highcharts(data);
                console.log(data);
            }
        });
        hideProgressBar();
    }

    function youthFarmers(name) {

        token = "youth_farmers";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token: token,
                crop: name
            },
            success: function (data) {
                $('#youth_farmers').highcharts(data);
            }
        });
        hideProgressBar();
    }

    function averageYield(name) {

        token = "average_yield";
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "form_actions/loadDairyCharts.php", //Relative or absolute path to file
            data: {
                token:token,
                farmers: "farmers",
                crop: name
            },
            success: function (data) {
                $('#average_yield').highcharts(data);
            }
        });
//        hideProgressBar();
    }

    function getLoadOutGrowerDash() {

        token = "outgrower_dash";
        $.ajax({
            type: "POST",
            url: "form_actions/recordDairyOutgrower.php",
            data: {token: token},
            success: function (data) {
                $("#outgrower_dash").html(data);

            }

        });

    }

    //outgrower_dash
    function Export2Csv(id) {

        var theForm, newInput1;
        // Start by creating a <form>
        theForm = document.createElement('form');
        theForm.action = 'export_dataset2csv.php';
        theForm.method = 'post';
        // Next create the <input>s in the form and give them names and values
        newInput1 = document.createElement('input');
        newInput1.type = 'hidden';
        newInput1.name = 'id';
        newInput1.value = id;


        // Now put everything together...
        theForm.appendChild(newInput1);

        // ...and it to the DOM...
        document.getElementById('hidden_form_container').appendChild(theForm);
        // ...and submit it
        theForm.submit();

    }
    function showProgressBar() {
        $(".cssload-container").show();
    }
    function hideProgressBar() {
        $(".cssload-container").hide();
    }
    function showempty() {
        $("#empty-message").show();
    }
    function hideempty() {
        $("#empty-message").hide();
    }

</script>
