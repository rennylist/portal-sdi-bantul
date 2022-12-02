<?php

class M_utility extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * Konversi Bilangan
     * 
     */

    //konversi dari angka ke huruf
    public function konversi_angka_huruf($angka) {
        $angka = (float) $angka;
        $bilangan = array(
            '',
            'Satu',
            'Dua',
            'Tiga',
            'Empat',
            'Lima',
            'Enam',
            'Tujuh',
            'Delapan',
            'Sembilan',
            'Sepuluh',
            'Sebelas'
        );

        if ($angka < 12) {
            return $bilangan[$angka];
        } else if ($angka < 20) {
            return $bilangan[$angka - 10] . ' belas';
        } else if ($angka < 100) {
            $hasil_bagi = (int) ($angka / 10);
            $hasil_mod = $angka % 10;
            return trim(sprintf('%s Puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
        } else if ($angka < 200) {
            return sprintf('Seratus %s', $this->konversi_angka_huruf($angka - 100));
        } else if ($angka < 1000) {
            $hasil_bagi = (int) ($angka / 100);
            $hasil_mod = $angka % 100;
            return trim(sprintf('%s Ratus %s', $bilangan[$hasil_bagi], $this->konversi_angka_huruf($hasil_mod)));
        } else if ($angka < 2000) {
            return trim(sprintf('Seribu %s', $this->konversi_angka_huruf($angka - 1000)));
        } else if ($angka < 1000000) {
            $hasil_bagi = (int) ($angka / 1000);
            $hasil_mod = $angka % 1000;
            return sprintf('%s Ribu %s', $this->konversi_angka_huruf($hasil_bagi), $this->konversi_angka_huruf($hasil_mod));
        } else if ($angka < 1000000000) {
            $hasil_bagi = (int) ($angka / 1000000);
            $hasil_mod = $angka % 1000000;
            return trim(sprintf('%s Juta %s', $this->konversi_angka_huruf($hasil_bagi), $this->konversi_angka_huruf($hasil_mod)));
        } else if ($angka < 1000000000000) {
            $hasil_bagi = (int) ($angka / 1000000000);
            $hasil_mod = fmod($angka, 1000000000);
            return trim(sprintf('%s Milyar %s', $this->konversi_angka_huruf($hasil_bagi), $this->konversi_angka_huruf($hasil_mod)));
        } else if ($angka < 1000000000000000) {
            $hasil_bagi = $angka / 1000000000000;
            $hasil_mod = fmod($angka, 1000000000000);
            return trim(sprintf('%s Triliun %s', $this->konversi_angka_huruf($hasil_bagi), $this->konversi_angka_huruf($hasil_mod)));
        } else {
            return 'Jumlah terlalu panjang';
        }
    }

    //format tampilan hasil konversi
    public function format_konversi($terbilang = "", $style = "") {
        switch ($style) {
            case 1:
                //format uppercase
                $terbilang = strtoupper($terbilang);
                break;
            case 2:
                //format lowercase
                $terbilang = strtolower($terbilang);
                break;
            case 3:
                //format huruf pertama setiap kata uppercase
                $terbilang = ucwords($terbilang);
                break;
            default:
                //format huruf pertama uppercase
                $terbilang = ucfirst($terbilang);
                break;
        }
        return $terbilang;
    }

    /*
     * CALENDAR
     */

    function calendar_num_weeks($month, $year) {
        // every month has at least 4 weeks
        $num_weeks = 4;

        // finding where it starts
        $first_day = $this->calendar_first_day($month, $year);

        // if the first week doesn't start on monday 
        // we are sure that the month has at minimum 5 weeks
        if ($first_day != 1)
            $num_weeks++;

        // find the "widow" days (i.e. empty cells in the 1st week)
        $widows = $first_day - 1;
        $fw_days = 7 - $widows;
        if ($fw_days == 7)
            $fw_days = 0;

        // number of days in the month
        $numdays = date("t", mktime(2, 0, 0, $month, 1, $year));

        if (($numdays - $fw_days) > 28)
            $num_weeks++;
        // that's it!
        return $num_weeks;
    }

    function calendar_first_day($month, $year) {
        $first_day = date("w", mktime(2, 0, 0, $month, 1, $year));
        if ($first_day == 0)
            $first_day = 7;# convert Sunday

        return $first_day;
    }

    function calendar_days($month, $year, $week, $num_weeks = 0) {
        $days = array();

        // this is just to avoid calling num_weeks every time 
        // when you loop through the weeks        
        if ($num_weeks == 0)
            $num_weeks = $this->calendar_num_weeks($month, $year);

        // find which day of the week is 1st of the given month        
        $first_day = $this->calendar_first_day($month, $year);

        // find widow days (first week)
        $widows = $first_day - 1;

        // first week days
        $fw_days = 7 - $widows;

        // if $week==1 don't do further calculations
        if ($week == 1) {
            for ($i = 0; $i < $widows; $i++) {
                $days[] = 0;
            }
            for ($i = 1; $i <= $fw_days; $i++) {
                $day = (strlen($i) < 2) ? '0' . $i : $i;
                $days[] = $day;
            }
            return $days;
        }

        // any other week
        if ($week != $num_weeks) {
            $first = $fw_days + (($week - 2) * 7);
            for ($i = $first + 1; $i <= $first + 7; $i++) {
                $day = (strlen($i) < 2) ? '0' . $i : $i;
                $days[] = $day;
            }
            return $days;
        }


        # only last week calculations below
        // number of days in the month
        $numdays = date("t", mktime(2, 0, 0, $month, 1, $year));

        // find orphan days (last week)  
        $orphans = $numdays - $fw_days - (($num_weeks - 2) * 7);
        $empty = 7 - $orphans;
        for ($i = ($numdays - $orphans) + 1; $i <= $numdays; $i++) {
            $day = (strlen($i) < 2) ? '0' . $i : $i;
            $days[] = $day;
        }
        for ($i = 0; $i < $empty; $i++) {
            $days[] = 0;
        }
        return $days;
    }

}
