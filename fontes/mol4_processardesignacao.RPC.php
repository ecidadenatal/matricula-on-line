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
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/JSON.php"));

$oJson                  = new services_json();
$oParam                 = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = '';

define('MSG_MOL4PROCESSARDESIGNACAORPC', "educacao.matriculaonline.mol4_processardesignacaoRPC.");

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "validaEnsinosFase":

      if ( empty($oParam->iFase) ) {
        throw new Exception( _M(MSG_MOL4PROCESSARDESIGNACAORPC . "informe_fase") );
      }

      $sWhere   = "     mo04_codigo = {$oParam->iFase} ";
      $sWhere  .= " and mo17_sequencial is null ";

      $sCampos  = " distinct ed10_c_descr ";
      $oDaoFase = new cl_fase();
      $sSqlFase = $oDaoFase->sql_query_criterios_ensino(null, $sCampos, null, $sWhere);
      $rsFase   = db_query($sSqlFase);

      $oMsgErro = new stdClass();
      if ( !$rsFase ) {

        $oMsgErro->sErro = pg_last_error();
        throw new DBException( _M(MSG_MOL4PROCESSARDESIGNACAORPC . "erro_validar_fase", $oMsgErro) );
      }
      $iLinhas              = pg_num_rows($rsFase);
      $aEnsinosSemCriterios = array();
      for ($i=0; $i < $iLinhas; $i++) {
        $aEnsinosSemCriterios[] = db_utils::fieldsMemory($rsFase, $i)->ed10_c_descr;
      }

      if (count($aEnsinosSemCriterios) > 0 ) {

        $oMsgErro->sListaEnsinos = implode("\n", $aEnsinosSemCriterios);
        throw new Exception( _M(MSG_MOL4PROCESSARDESIGNACAORPC . "ensinos_sem_criterios", $oMsgErro) );

      }

      break;
    case "processarFase":

      if ( empty($oParam->iFase) ) {
        throw new Exception( _M(MSG_MOL4PROCESSARDESIGNACAORPC . "informe_fase") );
      }

      $oFase = new Fase($oParam->iFase);
      $oFase->processar();
      $oRetorno->sMessage = urlencode( _M(MSG_MOL4PROCESSARDESIGNACAORPC . "fase_processada_sucesso") );
      break;
  }

  db_fim_transacao(false);


} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}

$oRetorno->erro = $oRetorno->iStatus == 2;
echo $oJson->encode($oRetorno);