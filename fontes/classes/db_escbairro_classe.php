<?php

class cl_escbairro extends DAOBasica {

  public function __construct() {
    parent::__construct("escbairro");
  }

  public function sql_query_escola_bairro( $sCampos = "*", $sOrdem = null, $sWhere = "" ) {

    $sSql  = "select {$sCampos} ";
    $sSql .= "  from plugins.escbairro ";
    $sSql .= "       inner join escola on ed18_i_codigo = mo08_escola";
    $sSql .= "       inner join bairro on j13_codi      = mo08_bairro";

    if (!empty($sWhere)) {
      $sSql .= " where $sWhere";
    }

    if (!empty($sOrdem)) {
      $sSql .= " order by {$sOrdem}";
    }

    return $sSql;
  }
}