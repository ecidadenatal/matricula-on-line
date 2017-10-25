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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oJson      = new Services_JSON();
$oParam     = $oJson->decode(str_replace("\\","",$_POST["json"]));

$oRetorno          = new stdClass();
$oRetorno->status  = 1;
$oRetorno->message = '';
$oRetorno->erro    = false;

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case 'buscar':

      $oDaoVagas = new cl_vagas();

      $oRetorno->aVagas = array();

      $sCampos    = " ed11_i_codigo, ed11_c_descr ";
      $sWhere     = " ed10_i_codigo     = {$oParam->iEnsino} ";
      $sWhere    .= " AND ed85_i_escola = {$oParam->iEscola} ";
      $sWhere    .= " AND ed85_i_turno  = {$oParam->iTurno} ";
      $sWhere    .= " GROUP by ed11_i_codigo, mo10_codigo";
      $sOrdem     = " ed11_i_sequencia ";
      $sSqlSeries = $oDaoVagas->sql_query_vagas_etapa( null, $sCampos, $sOrdem, $sWhere );
      $rsSeries   = db_query($sSqlSeries);

      if ( !$rsSeries ) {
        throw new DBException("Erro ao buscar etapas!");
      }

      $oRetorno->aEtapas = array();

      if ( pg_num_rows($rsSeries) > 0 ) {

        for ($iContador = 0; $iContador < pg_num_rows($rsSeries); $iContador++) {


          $oDadosEtapas = db_utils::fieldsMemory($rsSeries, $iContador);

          $oEtapas              = new stdClass();
          $oEtapas->iEtapa      = $oDadosEtapas->ed11_i_codigo;
          $oEtapas->sEtapa      = urlencode($oDadosEtapas->ed11_c_descr);
          $oEtapas->iVagas      = 0;
          $oEtapas->iCodigoVaga = '';

          $oRetorno->aEtapas[$oDadosEtapas->ed11_i_codigo] = $oEtapas;
        }
      }

      $sCamposVagas    = " ed11_i_codigo, ";
      $sCamposVagas   .= " COALESCE(mo10_codigo, null) as codigo_vaga, ";
      $sCamposVagas   .= " COALESCE(mo10_numvagas,0) as total_vagas ";
      $sWhereVagas     = " mo10_ensino      = {$oParam->iEnsino} ";
      $sWhereVagas    .= " AND mo10_escola  = {$oParam->iEscola} ";
      $sWhereVagas    .= " AND mo10_turno   = {$oParam->iTurno} ";
      $sWhereVagas    .= " AND mo10_fase    = {$oParam->iFase} ";
      $sWhereVagas    .= " GROUP by ed11_i_codigo, mo10_codigo";
      $sOrdemVagas     = " ed11_i_codigo ";
      $sSqlVagas = $oDaoVagas->sql_query_vagas_etapa( null, $sCamposVagas, $sOrdemVagas, $sWhereVagas );
      $rsVagas   = db_query($sSqlVagas);

      if ( !$rsVagas ) {
        throw new DBException("Erro ao buscar vagas!");
      }

      $aEtapas = array();

      if ( pg_num_rows($rsVagas) > 0 ) {

        for ($iContador = 0; $iContador < pg_num_rows($rsVagas); $iContador++) {

          $oDadosVagas = db_utils::fieldsMemory($rsVagas, $iContador);

          $oRetorno->aEtapas[$oDadosVagas->ed11_i_codigo]->iVagas      = $oDadosVagas->total_vagas;
          $oRetorno->aEtapas[$oDadosVagas->ed11_i_codigo]->iCodigoVaga = $oDadosVagas->codigo_vaga;
        }
      }

      $oRetorno->aEtapas = array_values($oRetorno->aEtapas);

    break;
    case 'salvar':

      foreach($oParam->aEtapasVagas as $oVaga) {

        $oVagas = new Vagas( $oVaga->iCodigoVaga );
        $oVagas->setEnsino($oParam->iEnsino);
        $oVagas->setEscola($oParam->iEscola);
        $oVagas->setTurno($oParam->iTurno);
        $oVagas->setFase($oParam->iFase);
        $oVagas->setSerie( $oVaga->iEtapa );
        $oVagas->setNumVagas( $oVaga->iVagas );
        $oVagas->setSaldoVagas( $oVaga->iVagas );
        $oVagas->salvar();
      }

      db_fim_transacao(false);

    break;

  }

} catch (Exception $eErro) {

  db_fim_transacao(true);
  $oRetorno->status  = 2;
  $oRetorno->message = urlencode($eErro->getMessage());
  $oRetorno->erro    = true;
}

$oRetorno->erro = $oRetorno->status == 2;
echo $oJson->encode($oRetorno);