<style>
    .sekolah{
            line-height: 10%;
        }
        .garis{
            border: 2.5px solid;
            margin-top: -25px;
        }

        #jarak{
          line-height: 10%;
        }
</style>

@php
$sekolah = App\Models\Sekolah::find(1);
//dd($sekolah->logo_sekolah);
@endphp

<table id="judul" class="sekolah">
<tr>
<td>
  <h2>
    <img id ="showImage"src="{{$sekolah->logo_sekolah}}" width="100">

  </h2>
</td>
<td align="center" >
  <h3>
    DINAS PROVINSI {{strtoupper($sekolah->provinsi)}}
  </h3>
  <h2>
    {{$sekolah->nama}}
  </h2>
  <p>{{$sekolah->alamat}}</p>
  <p>Email : {{$sekolah->email}}, Website : {{$sekolah->website}}, </p>


</td>
<td>
  <h2>
    <img id ="showImageProvinsi"src="{{$sekolah->logo_provinsi}}" width="80">

  </h2>
</td>
</tr>
</table>
<hr class="garis" id="jarak" />
