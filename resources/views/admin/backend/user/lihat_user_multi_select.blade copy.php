<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Select 2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div class="page-content">
    <div class="card">
        <div class="card-body">
				<div class="mb-4">
					<div class="col-xl-12 mx-auto">
                        
						<h6 class="mb-0 text-uppercase">Multiple select</h6>
						 <hr/>
						<div class="card">
						   <div class="card-body">
                                <form action="">
                                    <div class="mb-12">
                                    <label for="multiple-select-field" class="form-label">Basic multiple select</label>
                                    <select class="tags form-select" 
                                    id="tags" 
                                    data-placeholder="Choose anything"
                                    name="tags[]" 
                                    multiple="multiple">
                                    </select>
                                    </div>
                                </form>
							


						   </div>
						</div>

					</div>
				</div>
				<!--end mb-4-->
        </div>
    </div>

</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('.tags').select2({
            placeholder: 'Select',
            allowClear: true

        });

        $('#tags').select2({
            ajax:{
                url: "{{ route('get.user') }}",
                    type: "post",
                    dataType:'json',
                    delay:250,
                    data: function(params){
                        return{
                            name:params.term,
                            "_token":"{{ csrf_token() }}",
                        };
                    },

                    processResults:function(data){
                        return {
                            results: $.map(data, function(item){
                                return{
                                    id:item.id,
                                    text:item.name
                                }
                            })

                        };
                    },
            },
        });

	});

</script>
</body>
</html>
		