<?php

class cl_criteriosdesignacao extends DAOBasica {

  public function __construct() {
    parent::__construct("criteriosdesignacao");
  }


  public function sql_query_ensino ($mo16_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

    $sql  = " select {$campos} ";
    $sql .= "   from plugins.criteriosdesignacao ";
    $sql .= "   left join plugins.criteriosdesignacaoensino on mo17_criteriosdesignacao = mo16_sequencial ";
    $sql2 = "";
    if (empty($dbwhere)) {
      if (!empty($mo16_sequencial)){
        $sql2 .= " where plugins.criteriosdesignacao.mo16_sequencial = $mo16_sequencial ";
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