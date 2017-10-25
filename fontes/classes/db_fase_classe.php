<?php

class cl_fase extends DAOBasica {

  public function __construct() {
    parent::__construct("fase");
  }

  function sql_query_ensino_escola( $sCampos = '*', $sOrder, $sWhere ) {

    $sql  = "select {$sCampos}";
    $sql .= "  from plugins.fase \n";
    $sql .= "       inner join plugins.ciclos       on plugins.ciclos.mo09_codigo      = plugins.fase.mo04_ciclo          \n";
    $sql .= "       inner join plugins.ciclosensino on plugins.ciclosensino.mo14_ciclo = plugins.ciclos.mo09_codigo       \n";
    $sql .= "       inner join ensino               on ensino.ed10_i_codigo            = plugins.ciclosensino.mo14_ensino \n";
    $sql .= "       inner join cursoedu             on cursoedu.ed29_i_ensino          = ensino.ed10_i_codigo             \n";
    $sql .= "       inner join cursoescola          on cursoescola.ed71_i_curso        = cursoedu.ed29_i_codigo           \n";
    $sql .= "       inner join escola               on escola.ed18_i_codigo            = cursoescola.ed71_i_escola        \n";

    if (!empty($sWhere)) {
      $sql .= " where {$sWhere} ";
    }

    if (!empty($sOrder)) {
      $sql .= " order by {$sOrder}";
    }

    return $sql;
  }
  
  public function sql_query_criterios_ensino ($mo04_codigo = null,$campos = "*", $ordem = null, $dbwhere = "") {

    $sql  = " select {$campos}";
    $sql .= "   from fase ";
    $sql .= "  inner join plugins.ciclos                    on plugins.ciclos.mo09_codigo       = fase.mo04_ciclo           ";
    $sql .= "  inner join plugins.ciclosensino              on plugins.ciclosensino.mo14_ciclo  = ciclos.mo09_codigo        ";
    $sql .= "  inner join ensino                            on ensino.ed10_i_codigo     = ciclosensino.mo14_ensino          ";
    $sql .= "   left join plugins.criteriosdesignacaoensino on criteriosdesignacaoensino.mo17_ensino = ensino.ed10_i_codigo ";

    $sql2 = "";
    if (empty($dbwhere)) {

      if (!empty($mo04_codigo)) {
        $sql2 .= " where fase.mo04_codigo = $mo04_codigo ";
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