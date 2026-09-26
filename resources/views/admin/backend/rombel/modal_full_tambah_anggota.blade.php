                        <div class="col">

                            <div class="modal fade" id="exampleFullScreenModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tambah Anggota Rombel</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="myForm" method="post" action="{{route('simpan.anggota.rombel')}}" enctype="multipart/form-data">
                                                @csrf

                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Program Keahlian </label>
                                                    <select name="proka_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option selected="" disabled>Pilih Proka</option>
                                                        @foreach ($proka as $item )
                                                        <option value="{{$item->id}}">{{$item->nama_proka}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>

                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Nama Jurusan </label>
                                                    <select name="jurusan_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option> </option>

                                                    </select>
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Rombel </label>
                                                    <select name="rombel_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option> </option>

                                                    </select>
                                                </div>

                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Anggota Rombel </label>
                                                    <select name="siswa_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option selected="" disabled>Pilih Anggota Rombel</option>
                                                        @foreach ($siswa as $item )
                                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>


                                                <div class="mb-3">
                                                    <button type="submit" class="px-5 btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <script>
    //button create post event
    $('body').on('click', '#btn-create-post', function () {

        //open modal
        $('#modal-create').modal('show');
    });

    //action create post
    $('#store').click(function(e) {
        e.preventDefault();

        //define variable
        let title   = $('#title').val();
        let content = $('#content').val();
        let token   = $("meta[name='csrf-token']").attr("content");
        
        //ajax
        $.ajax({

            url: `/posts`,
            type: "POST",
            cache: false,
            data: {
                "title": title,
                "content": content,
                "_token": token
            },
            success:function(response){

                //show success message
                Swal.fire({
                    type: 'success',
                    icon: 'success',
                    title: `${response.message}`,
                    showConfirmButton: false,
                    timer: 3000
                });

                //data post
                let post = `
                    <tr id="index_${response.data.id}">
                        <td>${response.data.title}</td>
                        <td>${response.data.content}</td>
                        <td class="text-center">
                            <a href="javascript:void(0)" id="btn-edit-post" data-id="${response.data.id}" class="btn btn-primary btn-sm">EDIT</a>
                            <a href="javascript:void(0)" id="btn-delete-post" data-id="${response.data.id}" class="btn btn-danger btn-sm">DELETE</a>
                        </td>
                    </tr>
                `;
                
                //append to table
                $('#table-posts').prepend(post);
                
                //clear form
                $('#title').val('');
                $('#content').val('');

                //close modal
                $('#modal-create').modal('hide');
                

            },
            error:function(error){
                
                if(error.responseJSON.title[0]) {

                    //show alert
                    $('#alert-title').removeClass('d-none');
                    $('#alert-title').addClass('d-block');

                    //add message to alert
                    $('#alert-title').html(error.responseJSON.title[0]);
                } 

                if(error.responseJSON.content[0]) {

                    //show alert
                    $('#alert-content').removeClass('d-none');
                    $('#alert-content').addClass('d-block');

                    //add message to alert
                    $('#alert-content').html(error.responseJSON.content[0]);
                } 

            }

        });

    });

</script>
