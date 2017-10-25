<?php

class cl_vagas extends DAOBasica {

  public function __construct() {
    parent::__construct("vagas");
  }

  /**
   * Liga as vagas com as series, escolas e ensinos vinculados
   * @param  integer $mo10_codigo
   * @param  string $campos
   * @param  string $ordem
   * @param  string $dbwhere
   */
  public function sql_query_escola_serie_ensino ($mo10_codigo = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from plugins.vagas ";
     $sql .= "      inner join plugins.fase on plugins.fase.mo04_codigo = plugins.vagas.mo10_fase";
     $sql .= "      inner join serie        on serie.ed11_i_codigo      = plugins.vagas.mo10_serie ";
     $sql .= "      inner join escola       on escola.ed18_i_codigo     = plugins.vagas.mo10_escola ";
     $sql .= "      inner join ensino       on ensino.ed10_i_codigo     = plugins.vagas.mo10_ensino ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($mo10_codigo)){
        $sql2 .= " where plugins.vagas.mo10_codigo = $mo10_codigo ";
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


  public function sql_query_vagas_etapa ($ed11_i_codigo = null,$campos = "*", $ordem = null, $dbwhere = "") {

    $sql  = "select {$campos}";
    $sql .= "  from serie ";
    $sql .= "      left  join plugins.vagas on plugins.vagas.mo10_serie = serie.ed11_i_codigo ";
    $sql .= "      inner join ensino        on ensino.ed10_i_codigo     = serie.ed11_i_ensino";
    $sql .= "      inner join cursoedu      on cursoedu.ed29_i_ensino   = ensino.ed10_i_codigo";
    $sql .= "      inner join cursoturno    on cursoturno.ed85_i_curso  = cursoedu.ed29_i_codigo";
    $sql2 = "";
    if (empty($dbwhere)) {
      if (!empty($ed11_i_codigo)) {
        $sql2 .= " where serie.ed11_i_codigo = $ed11_i_codigo ";
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