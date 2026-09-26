<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak User </title>
    <style>
        h1{
            text-align: center;

        }

        #user td, #user th {
            border: 1px solid #ddd;
            padding: 8px;
            }

        #user tr:nth-child(even){background-color: #f2f2f2;}

        #user tr:hover {background-color: #ddd;}

        #user th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }

    </style>
</head>
<body>

@include('admin.body.logo_sekolah_a4')
    <h1>Informasi Akun</h1>
    <table id="user">
        <tr>
            <th>Nama Pengguna</th>
            <th>Email</th>
            <th>Password</th>
            <th>Role</th>
        </tr>
        @foreach ($users as $key=> $user)
        <tr>
            <td>{{$user->name}}</td>
            <td>{{$user->email}}</td>
            <td>12345678</td>
            <td>
                @if ($user->jenis_user == 'siswa')
                    <span class="badge bg-success">Siswa</span>
                @elseif($user->jenis_user == 'guru')
                    <span class="badge bg-danger">Guru</span>
                @elseif($user->jenis_user == 'wakil')
                    <span class="badge bg-danger">Wakil</span>
                @elseif($user->jenis_user == 'admin')
                    <span class="badge bg-danger">Admin</span>
                @endif
            </td>
        </tr>
        @endforeach

    </table>
    @php
        $id = Auth::user()->id;
        // dd($id);
        $user = App\Models\User::find($id);
    @endphp
    <i style="font-size: 14px; float: left;">Tanggal Cetak : {{ date("d M Y") }}</i>
    <i style="font-size: 14px; float: right;">dicetak oleh : <a href="http://wa.me/6285274817886">{{$user->name }}</a></i>
</body>
</html>
