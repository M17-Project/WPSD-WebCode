<?php

class xGeoLookup {
   
   private $Flagarray               = [];
   private $Flagarray_DXCC          = [];
   private $Flagfile                = null;
   
   public function SetFlagFile($Flagfile) {
      if (file_exists($Flagfile) && (is_readable($Flagfile))) {
         $this->Flagfile = $Flagfile;
         return true;
      }
      return false;
   }
    
   public function LoadFlags() {
      if ($this->Flagfile === null) {
         return false;
      }

      $json_content = file_get_contents($this->Flagfile);
      if ($json_content === false) {
         return false;
      }

      $data = json_decode($json_content, true);
      if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
         return false;
      }

      $this->Flagarray = [];
      $this->Flagarray_DXCC = [];

      foreach ($data as $country_data) {
         $country_index = count($this->Flagarray);
         $this->Flagarray[$country_index] = [
            'Country' => $country_data['country_name'],
            'ISO' => $country_data['country_code']
         ];

         foreach ($country_data['prefixes'] as $prefix_entry) {
            $this->addPrefixToDXCC($prefix_entry, $country_index);
         }

         if (isset($country_data['sub_entities']) && is_array($country_data['sub_entities'])) {
            foreach ($country_data['sub_entities'] as $sub_entity_data) {
               $sub_entity_index = count($this->Flagarray);
               $this->Flagarray[$sub_entity_index] = [
                  'Country' => $sub_entity_data['name'],
                  'ISO' => $country_data['country_code']
               ];
               foreach ($sub_entity_data['prefixes'] as $prefix_entry) {
                  $this->addPrefixToDXCC($prefix_entry, $sub_entity_index);
               }
            }
         }
      }
      return true;
   }
   
   private function addPrefixToDXCC($prefix_entry, $index) {
        if (strpos($prefix_entry, '-') === false) {
            $this->Flagarray_DXCC[$prefix_entry] = $index;
        } else {
            list($start_prefix, $end_prefix) = explode('-', $prefix_entry);
            
            $start_len = strlen($start_prefix);
            $end_len = strlen($end_prefix);

            if ($start_len === 2 && $end_len === 2) { 
                $first_char_start = ord($start_prefix[0]);
                $first_char_end = ord($end_prefix[0]);
                $second_char_start = ord($start_prefix[1]);
                $second_char_end = ord($end_prefix[1]);

                for ($c1 = $first_char_start; $c1 <= $first_char_end; $c1++) {
                    $s2_start = ($c1 == $first_char_start) ? $second_char_start : ord('A');
                    $s2_end = ($c1 == $first_char_end) ? $second_char_end : ord('Z');
                    
                    for ($c2 = $s2_start; $c2 <= $s2_end; $c2++) {
                        $this->Flagarray_DXCC[chr($c1) . chr($c2)] = $index;
                    }
                }
            } else if ($start_len === 1 && $end_len === 1) {
                for ($i = ord($start_prefix); $i <= ord($end_prefix); $i++) {
                    $this->Flagarray_DXCC[chr($i)] = $index;
                }
            }
        }
   }
   
   public function GetFlag($callsign) {
      $Image     = "";
      $Name = "";
      
      for ($Letters = 6; $Letters >= 1; $Letters--) {
         $Prefix = strtoupper(substr(trim($callsign), 0, $Letters));
         
         if (isset($this->Flagarray_DXCC[$Prefix])) {
            $index = $this->Flagarray_DXCC[$Prefix];
            $Image = $this->Flagarray[$index]['ISO'];
            $Name  = $this->Flagarray[$index]['Country'];
            return array(strtolower($Image), $Name);
         }
      }
      
      return array("undefined", "Undefined"); 
   }
} 

