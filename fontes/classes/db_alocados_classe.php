<?php

class cl_alocados extends DAOBasica {

  public function __construct() {
    parent::__construct("alocados");
  }


  /**
   * Dados dos alunos alocados
   * @param  integer $mo13_codigo pk
   * @param  string  $campos
   * @param  string  $ordem
   * @param  string  $dbwhere
   * @return string               sql
   */
  public function sql_query_dadosaluno ($mo13_codigo = null, $campos = "*", $ordem = null, $dbwhere = "") {

    $sql  = " select {$campos} ";
    $sql .= "   from plugins.alocados ";
    $sql .= "  inner join plugins.fase         on plugins.fase.mo04_codigo         = plugins.alocados.mo13_fase ";
    $sql .= "  inner join plugins.mobase       on plugins.mobase.mo01_codigo       = plugins.alocados.mo13_base ";
    $sql .= "  inner join plugins.redeorigem   on plugins.redeorigem.mo05_codigo   = plugins.mobase.mo01_redeorigem ";
    $sql .= "  inner join plugins.baseescturno on plugins.baseescturno.mo03_codigo = plugins.alocados.mo13_baseescturno ";
    $sql .= "  inner join plugins.ciclos       on plugins.ciclos.mo09_codigo       = plugins.fase.mo04_ciclo ";
    $sql .= "  inner join bairro               on bairro.j13_codi                  = plugins.mobase.mo01_bairro ";
    $sql .= "  inner join serie                on serie.ed11_i_codigo              = plugins.mobase.mo01_serie ";
    $sql .= "  inner join baseescola           on baseescola.mo02_codigo           = baseescturno.mo03_baseescola ";
    $sql .= "  inner join escola               on escola.ed18_i_codigo             = baseescola.mo02_escola ";
    $sql .= "  inner join turno                on turno.ed15_i_codigo              = baseescturno.mo03_turno ";

    $sql2 = "";
    if (empty($dbwhere)) {
      if (!empty($mo13_codigo)){
        $sql2 .= " where alocados.mo13_codigo = $mo13_codigo ";
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