<?php

class cl_mobase extends DAOBasica {

  public function __construct() {
    parent::__construct("mobase");
  }

  public function sql_query_inscritos ($mo01_codigo = null, $campos = "*", $ordem = null, $dbwhere = "") {

    $sql  = " select {$campos} ";
    $sql .= "   from plugins.mobase    ";
    $sql .= "  inner join plugins.basefase     on mo12_base       = mo01_codigo  ";
    $sql .= "  inner join plugins.fase         on mo04_codigo     = mo12_fase    ";
    $sql .= "  inner join plugins.baseescola   on mo02_base       = mo01_codigo  ";
    $sql .= "  inner join plugins.baseescturno on mo03_baseescola = mo02_codigo  ";
    $sql .= "  inner join serie                on ed11_i_codigo   = mo01_serie   ";
    $sql .= "  inner join ensino               on ed10_i_codigo   = ed11_i_ensino";
    $sql .= "  inner join escola               on ed18_i_codigo   = mo02_escola  ";

    $sql2 = "";
    if (empty($dbwhere)) {

      if (!empty($mo01_codigo)) {
        $sql2 .= " where plugins.mobase.mo01_codigo = $mo01_codigo ";
      }
    } else if (!empty($dbwhere)) {
      $sql2 = " where $dbwhere";
    }
    $sql .= $sql2;
    if (!empty($ordem)) {
      $sql .= " order by {$ordem}";
    }
    return $sql;
  }
}