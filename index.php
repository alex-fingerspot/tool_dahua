<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <script src="js/excellentexport.js"></script>
    <script src="js/jszip.js"></script>
    <script src="js/xlsx.js"></script>

</head>
<body>
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    
    
    
    <div class="alert alert-info text-center"><h4>Tool impor masal dahua</h4></div>

    <div class="container">

        <div class="form-group">
            excel
            <input type="file" class="form-control" id="file_excel" name="file_excel" accept=".xlsx,.xls">
        </div>


        <div id='info_import'>
            <hr>

            <div class="alert alert-warning">
                <div class="row">
                    <div class="col-md-6" style="font-weight:bold;">
                        Total Data : <span id="count_total">0</span>
                        <br>
                        Gagal : <span id="count_fail">0</span>
                        <br>
                        Sukses : <span id="count_success">0</span>
                    </div>
                    <div class="col-md-6 pt-3">
                        <div class="btn btn-success" style="width:100%;" id="buton" onclick="start_import()">Import</div>
                    </div>
                </div>
            </div>
            <hr>
            <table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Pin</th>
                        <th>Nama</th>
                        <th>Pass</th>
                        <th>Foto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id='tbody_impor'>
                    <!-- from js -->
                </tbody>

            </table>
            <!-- from js -->
        </div>
    </div>

    <script>

        var rows = [];

        var icon_0 = "Klik tombol import";
        var icon_1 = "<i class='fa fa-lg fa-spin fa-circle-o-notch'></i>";
        var icon_2 = "<i class='fa fa-lg fa-check text-success'></i>";
        var icon_3 = "<i class='fa fa-lg fa-times text-danger'></i>";
        
        var ExcelToJSON = function() {

            this.parseExcel = function(file) {
                var reader = new FileReader();
                var file_name = file.name;
                var file_name_arr = file_name.split("_");
                var cloud_id_file = "";

                if(typeof file_name_arr[3] !== 'undefined'){
                    if(typeof file_name_arr[4] !== 'undefined'){
                        cloud_id_file = file_name_arr[2];
                    }
                    else{
                        cloud_id_file = file_name_arr[3];
                    }
                }

                reader.onload = function(e) {

                    var data = e.target.result;

                    var read_success = false;
                    try{
                        var workbook = XLSX.read(data, { type: 'binary' });
                        read_success = true;
                    }catch(err){
                        console.log(err);
                        $("#btn_submit_import_excel").css('display','none');
                        alert("impor gagal");
                    }

                    if(read_success){
                        $("#btn_submit_import_excel").css('display','block');

                        var nama_sheet = workbook.SheetNames[0];
                        // Here is your object
                        var XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[nama_sheet]);
                        // check format
                        const column_format_valid = true;

                        // save 
                        rows = XL_row_object;

                        if(column_format_valid){
                            console.log(XL_row_object);
                            var html_tbody = '';
                            for(var i = 0 ; i < XL_row_object.length ; i++){
                                var obj = XL_row_object[i];
                                html_tbody += `
                                    <tr>
                                        <td>${obj.pin}</td>
                                        <td>${obj.nama}</td>
                                        <td>${obj.pass}</td>
                                        <td>${obj.foto}</td>
                                        <td id="status_${obj.pin}" class="text-center">${icon_0}</td>
                                    </tr>
                                `;
                            }

                            $('#tbody_impor').html(html_tbody);

                            // count
                            $("#count_total").html(XL_row_object.length);
                        }
                    }

                };

                reader.onerror = function(ex) {
                    console.log(ex);
                };

                reader.readAsBinaryString(file);
            };
        };

        function handleFileSelect(evt) {
            var files = evt.target.files; // FileList object
            var xl2json = new ExcelToJSON();
            xl2json.parseExcel(files[0]);
        }

        document.getElementById('file_excel').addEventListener('change', handleFileSelect, false);


        function start_import(){
            $('#buton').prop('disabled',true);
            ajax_import(rows, 0);
        }

        function ajax_import(rows, iteration){
            console.log(rows);
            if(iteration < rows.length){
                var row = rows[iteration];
                var pin = row.pin;
                var nama = row.nama;
                var pass = row.pass;
                var foto = row.foto;
                var hak = row.hak;

                $.ajax({
                    url       : "process.php",
                    data      : `pin=${pin}&nama=${nama}&pass=${pass}&foto=${foto}&hak=${hak}`,
                    headers   :{'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type      : 'GET',
                    beforeSend: function(){
                        $('#status_'+pin).html(icon_1);
                    },
                    success : function(data) {
                        try {
                            var res = jQuery.parseJSON(data);
                        }
                        catch(e) {
                            var res = data;
                        }

                        if(res.success == true) {
                            console.log("call_ajax_ success", new Date());

                            $('#status_'+pin).html(icon_2);
                            // count
                            var count_success = $('#count_success').html();
                            count_success++;
                            $('#count_success').html(count_success);
                            

                        } else {
                            var flag = 0;

                            console.log(res.msg);
                            if(typeof res.msg === "object"){
                                for (var key in res.msg) {
                                    if (flag == 0) {
                                        var obj = res.msg[key];

                                        console.log(obj.toString());
                                    }

                                    flag++;
                                };
                            }else{
                                console.log(res.msg.toString());
                            }

                            // icon
                            $('#status_'+pin).html(icon_3);
                            // count
                            var count_fail = $('#count_fail').html();
                            count_fail++;
                            $('#count_fail').html(count_fail);

                        }

                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    }
                });

                iteration++;

                ajax_import(rows, iteration)
            }
        }
    </script>
</body>
</html>