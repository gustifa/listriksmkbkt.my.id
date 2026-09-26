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
        .judul-tulisan{
          font-size: 14px;
          margin-top: -25px;
        }
</style>

@php
$sekolah = App\Models\Sekolah::find(1);
@endphp

<div id="">
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
  <h3>
    DINAS PENDIDIKAN
  </h3>
  <h2>
    {{$sekolah->nama}}
  </h2>
  <div class="judul-tulisan">
    <p>{{$sekolah->alamat}}</p>
    <p>Email: {{$sekolah->email}}, Website: <a href="http://{{$sekolah->website}}" target="_blank">{{$sekolah->website}}</a>, </p>
  </div>
  


</td>
<td>
  <h2>
    <img id ="showImageProvinsi"src="{{$sekolah->logo_provinsi}}" width="80">

  </h2>
</td>
</tr>
</table>
<hr class="garis" id="jarak" />
</div>