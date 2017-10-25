<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */


class VagasRepository {

  /**
   * Coleção de Vagas
   * @var array
   */
  private $aVagas = array();

  /**
   * Instancia da classe
   * @var VagasRepository
   */
  private static $oInstance;

  private function __construct() {}

  private function __clone() {}

  protected static function getInstance() {

    if ( self::$oInstance == null ) {
      self::$oInstance = new VagasRepository();
    }
    return self::$oInstance;
  }

  public static function getByCodigo($iCodigo) {

    if (!array_key_exists($iCodigo, VagasRepository::getInstance()->aVagas)) {
      VagasRepository::getInstance()->aVagas[$iCodigo] = new Vagas($iCodigo);
    }
    return VagasRepository::getInstance()->aVagas[$iCodigo];
  }

  public static function getByFase( $iFase ) {

    $oDaoVagas   = new cl_vagas();
    $sWhereVagas = " mo10_fase = {$iFase} ";
    $sSqlVagas   = $oDaoVagas->sql_query_file(null, " mo10_codigo ", null, $sWhereVagas);
    $rsVagas     = db_query($sSqlVagas);

    if ( !$rsVagas ) {
      throw new Exception("Erro ao buscar vagas da fase.\n" . pg_last_error());
    }
    $iLinhas = pg_num_rows($rsVagas);

    $aVagas = array();
    for( $i = 0; $i < $iLinhas; $i++) {
      $aVagas[] = self::getByCodigo(db_utils::fieldsMemory($rsVagas, $i)->mo10_codigo);
    }

    return $aVagas;
  }


  public static function getByFaseFilterEscolaEtapa( Fase $oFase, Escola $oEscola = null, Etapa $oEtapa = null ) {

    $aWhere   = array();
    $aWhere[] = " mo10_fase = {$oFase->getCodigo()} ";
    if ( !empty($oEscola) ) {
      $aWhere[] = " mo10_escola = {$oEscola->getCodigo()} ";
    }

    if ( !empty($oEtapa) ) {
      $aWhere[] = " mo10_serie = {$oEtapa->getCodigo()} ";
    }

    $sWhereVagas = implode(" and ", $aWhere);
    $sOrdem      = " mo10_escola, ed10_ordem, ed11_i_sequencia ";
    $oDaoVagas   = new cl_vagas();
    $sSqlVagas   = $oDaoVagas->sql_query_escola_serie_ensino(null, " mo10_codigo ", $sOrdem, $sWhereVagas);
    $rsVagas     = db_query($sSqlVagas);

    if ( !$rsVagas ) {
      throw new Exception("Erro ao buscar vagas da fase.\n" . pg_last_error());
    }

    $iLinhas = pg_num_rows($rsVagas);

    $aVagas = array();
    for( $i = 0; $i < $iLinhas; $i++) {
      $aVagas[] = self::getByCodigo(db_utils::fieldsMemory($rsVagas, $i)->mo10_codigo);
    }

    return $aVagas;
  }
}