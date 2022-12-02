<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Unduh</title>
    <style type="text/css">
        /*css reset*/
        html,
        body,
        div,
        span,
        applet,
        object,
        iframe,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        blockquote,
        pre,
        a,
        abbr,
        acronym,
        address,
        big,
        cite,
        code,
        del,
        dfn,
        em,
        img,
        ins,
        kbd,
        q,
        s,
        samp,
        small,
        strike,
        strong,
        sub,
        sup,
        tt,
        var,
        b,
        u,
        i,
        center,
        dl,
        dt,
        dd,
        ol,
        ul,
        li,
        fieldset,
        form,
        label,
        legend,
        table,
        caption,
        tbody,
        tfoot,
        thead,
        tr,
        th,
        td,
        article,
        aside,
        canvas,
        details,
        embed,
        figure,
        figcaption,
        footer,
        header,
        hgroup,
        menu,
        nav,
        output,
        ruby,
        section,
        summary,
        time,
        mark,
        audio,
        video {
            margin: 0;
            padding: 0;
            border: 0;
            vertical-align: baseline;
        }

        @font-face {
            font-family: 'arial';
            src: url({{ storage_path('fonts/arial.ttf')
        }
        }

        ) format('truetype');
        font-weight: 400;
        font-style: normal;
        }

        body {
            line-height: 1;
            font-family: 'arial', sans-serif;
        }


        ol,
        ul {
            list-style: none;
        }

        blockquote,
        q {
            quotes: none;
        }

        blockquote:before,
        blockquote:after,
        q:before,
        q:after {
            content: '';
            content: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        /*end of reset*/

        html {
            font-size: 11pt;
        }

        body {
            margin: 1cm 2cm 0cm 2cm;
            font-size: 11pt;
            line-height: 5;
        }

        table {
            background: #fff;
            padding: 1px;
        }

        tr,
        td {
            border: none;
            line-height: 1.7;
            vertical-align: top !important;

        }


        #right {
            border-right: none !important;
        }

        #left {
            border-left: none !important;
        }

        .isi {
            height: 300px !important;
        }

        .disp {
            text-align: center;
            /*padding-: 1.5rem 0;*/
            /*margin-bottom: .5rem;*/
        }

        .logo-opd {
            /* float: left; */
            position: relative;
            width: 105px;
            height: auto;
            margin: 0 0 0 0rem;
        }

        .jawa {
            max-width: 400px;
            height: 40px;
            margin: -5px 0px -10px 0px;
        }

        .alamat-telp {
            font-size: 1rem;
            margin-bottom: -6px;
        }

        .web-email {
            font-size: 1rem;
        }

        #lead {
            width: auto;
            position: relative;
            margin: 25px 0 0 75%;
        }

        .lead {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: -10px;
        }

        .tgh {
            text-align: center;
        }

        .alamat {
            font-size: 1rem;
        }

        .status {
            margin: 0;
            font-size: 1.3rem;
            margin-bottom: .5rem;
        }

        #lbr {
            font-size: 20px;
            font-weight: bold;
        }

        .separator {
            border-top: 2px solid #000;
            border-bottom: 4px solid #000;
            /*margin: -2rem 0 1rem;*/
            height: 2px;
        }

        .right {
            /* float: right; */
        }

        .paragraph {
            text-indent: 0.5rem;
            text-align: justify;
        }

        .space-right td {
            padding-right: 0.5rem;
        }

        .spasi-ttd tr td {
            line-height: 1.2;
        }

        .spasi-head tr td {
            line-height: 1.5;
        }

        .table-data tr td {
            /* padding-left: 0pt; */
            text-indent: 0pt;
        }

        .underline {
            text-decoration: underline;
        }

        .italic td tr {
            font-style: italic;
        }

        .new-line {
            clear: both;
        }

        .border-line tr td {
            border-style: solid;
            border-width: 1pt;
        }

        .table-border tr td {
            padding-left: 5pt;
            border: 1px solid black;
        }

        .table-border tr th {
            padding-left: 5pt;
            border: 1px solid black;
        }

        .table-transparan tr td {
            padding-left: 5pt;
            border: 0px;
        }

        .table-transparan tr th {
            padding-left: 5pt;
            border: 0px;
        }

        .nextpage {
            page-break-before: always;
        }

        .up {
            text-transform: uppercase;
            font-size: 11pt;
            line-height: 1.1;
        }

        .opd {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 13pt;
            line-height: 1.1;
        }

        /* 
        .pindah-hal {
            page-break-after: always;
        } */

        .ttd-agenda {
            min-height: 100px;
        }

        .garis {
            border: 1px solid #000;
        }

        #noborder td {
            border-top: none;
            border-bottom: none;
        }

        #noborder-top td {
            border-top: none;
        }

        #noborder-btm td {
            border-bottom: none;
        }

        /*tr td{*/
        /*    border: #0b2e13 1px solid;*/
        /*}*/
    </style>
</head>

<body>
    <div id="colres">

        <!-- LEMBAR KE 1 -->
        <div class="pindah-hal">

            <!-- KOP ATAS -->
            <div class="disp">
                <table style="width: 100%">
                    <tr>
                        <td align="center" style="width: 10%"><img class="logo-opd" src="{{ public_path(). $logo }}" /></td>
                        <td align="center" style="width: 90%">
                            <div class="up"><b>$nama_kabupaten</b></div>
                            <div class="opd" id="nama">{{ $nama_instansi }}</div>
                            <img class="jawa" src="{{ public_path(). $aksara_instansi }}" /><br>
                            <div class="alamat-telp">{{ $alamat }} Telp: {{ $telepon }}</div>
                            <div class="web-email">Website: {{ $website }} | E-mail: {{ $email }}</div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="separator"></div>
            <!-- END KOP -->

            <p style="text-align: center; line-height: 2;">BERITA ACARA</p>
            <p style="text-align: center; line-height: 1; padding-bottom: 10px;">PUBLIKASI DATA</p>
            <p style="text-align: center; line-height: 1;">Nomor : <?= $ba_nomor; ?> </p>
            <!--<p style="text-align: center; line-height: 0.5;">---------------------------------------------------------</p>-->



            <!-- TABEL DATA -->
            <table class="table-transparan" style="width: 100%">
                <tr>
                    <td style="width: 5%">&nbsp;</td>
                    <td colspan="2">Pada hari <?= $ba_day; ?>, tanggal <?= $ba_date; ?>, bulan <?= $ba_month; ?>, tahun <?= $ba_year; ?>, kami yang bertanda tangan di


                    </td>

                </tr>
                <tr>
                    <td colspan="3"> bawah ini :</td>
                </tr>

                <tr>
                    <td>I.</td>
                    <td style="width: 30%">Nama
                        <br>NIP
                        <br>Pangkat/Golongan
                        <br>Jabatan
                        <br>Alamat

                    </td>
                    <td>: <?= $pimpinan_name; ?>
                        <br>: <?= $pimpinan_nip; ?>
                        <br>: <?= $pimpinan_pangkat; ?>
                        <br>: <?= $pimpinan_jabatan; ?>
                        <br>: <?= $instansi_alamat; ?>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2">Selanjutnya disebut sebagai PIHAK PERTAMA
                    </td>

                </tr>

                <tr>
                    <td>II.</td>
                    <td style="width: 30%">Nama
                        <br>NIP
                        <br>Pangkat/Golongan
                        <br>Jabatan
                        <br>Alamat

                    </td>
                    <td>: <?= $nama; ?>
                        <br>: <?= $nip; ?>
                        <br>: <?= $pangkat; ?>
                        <br>: <?= $jabatan; ?>
                        <br>: <?= $alamat2; ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2">Selanjutnya disebut sebagai PIHAK KEDUA
                    </td>

                </tr>
            </table>
            <table class="table-transparan" style="width: 100%">
                <tr>
                    <td colspan="3">Menyatakan bahwa :
                    </td>
                </tr>
                <tr>
                    <td style="width: 5%">1.<br><br><br>2.<br><br>3.<br><br>4.<br><br>5.<br><br><br>6.</td>
                    <td colspan="2">PIHAK PERTAMA telah menyampaikan data kepada PIHAK KEDUA <b>sejumlah 2 (dua) indikator, 16 (enam belas) variabel, 0 (nol) sub variabel, dan 0 (nol) sub sub variabel </b> melalui portal https://data.bantulkab.go.id/ dengan rincian sebagaimana terlampir;
                        <br>PIHAK PERTAMA menjamin kebenaran dan kualitas data sebagaimana tersebut pada butir (1);
                        <br>PIHAK KEDUA telah melakukan pemeriksaan data sebagaimana tersebut pada butir (1) dengan hasil seluruh data dinyatakan telah sesuai dengan prinsip Satu Data Indonesia;
                        <br>PIHAK PERTAMA menjamin data yang bersifat publik sebagaimana tersebut pada butir (1) bukan termasuk klasifikasi daftar informasi yang dikecualikan;
                        <br>PIHAK KEDUA mempublikasi data yang bersifat publik dan telah dinyatakan sesuai sebagaimana tersebut pada butir (3) melalui portal http://data.bantulkab.go.id/ segera setelah berita acara ini;
                        <br>Data yang telah dipublikasi dalam portal sebagaimana tersebut pada butir (5) apabila dikemudian hari terdapat perubahan oleh PIHAK PERTAMA, maka akan dilakukan pemeriksaan kembali oleh PIHAK KEDUA.
                    </td>

                </tr>
                <tr>
                    <td colspan="3">Demikian Berita Acara ini dibuat dalam 2 (dua) rangkap, untuk dipergunakan sebagaimana mestinya.
                    </td>
                </tr>

            </table>

            <!-- END TABEL DATA -->

            <table class="table-transparan" style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 50%">&nbsp;</td>

                        <td style="width: 50%">Dibuat di ..............., ................ 2022</td>
                    </tr>
                    <tr>
                        <td align="center" style="width: 50%">Pihak Kedua
                            <br><br> <br> <u>NAMA TTD 2</u>
                        </td>

                        <td align="center" style="width: 50%">Pihak Pertama
                            <br><br> <br> <u>NAMA TTD 1</u>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="2" align="center">Mengetahui/Mengesahkan
                            <br><br><br>Drs. HELMI
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>


        </div>
        <!-- END LEMBAR KE 1 -->
        <!-- <div class="pindah-hal">
            <p style="text-align: left; line-height: 1;">Lampiran Berita Acara Publikasi Data </p>
            <p style="text-align: left; line-height: 1;">Nomor : <?= $ba_nomor; ?> </p>
            <p style="text-align: left; line-height: 1; padding-bottom: 10px;">Tanggal : <?= $ba_nomor; ?> </p>

            <table class="table-border" style="width: 100%;">
                <tbody>
                    <tr>
                        <td>Kode</td>
                        <td>Nama Data</td>
                        <td>Indikator/Variabel/Sub Variabel</td>
                        <td>Tahun</td>
                        <td>Satuan</td>
                        <td>Nilai Data</td>
                        <td>Status Data</td>
                    </tr>

                </tbody>
            </table>
        </div> -->
    </div>


</body>

</html>