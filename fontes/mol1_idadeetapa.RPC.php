<?php
/**
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

$oJson               = new services_json();
$oParam              = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno            = new stdClass();
$oRetorno->iStatus   = 1;
$oRetorno->sMensagem = '';
$oRetorno->erro      = false;
define("MENSAGEM_IDADE_ETAPA", "educacao.matriculaonline.mol1_idadeetapa_RPC.");
try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case 'getEtapas':

      $aEtapas        = EtapaRepository::getEtapasEnsino(new Ensino((int)$oParam->iCodigoEnsino));
      $aIdadePorEtapa = array();
      foreach ($aEtapas as $oEtapa) {

        $oIdadeEtapa = new IdadeEtapa($oEtapa);

        $oStdIdadeEtapa                = new stdClass();
        $oStdIdadeEtapa->codigo        = $oEtapa->getCodigo();
        $oStdIdadeEtapa->nome          = urlencode($oEtapa->getNome());

        $oStdIdadeEtapa->idade_inicial = array(
          "ano" => $oIdadeEtapa->getIdadeInicial()->getYears(),
          "mes" => $oIdadeEtapa->getIdadeInicial()->getMonths(),
        );

        $oStdIdadeEtapa->idade_final   = array(
          "ano" => $oIdadeEtapa->getIdadeFinal()->getYears(),
          "mes" => $oIdadeEtapa->getIdadeFinal()->getMonths(),
        );

        $aIdadePorEtapa[]              = $oStdIdadeEtapa;
      }
      $oRetorno->aEtapas = $aIdadePorEtapa;
      break;

    case 'salvar':

      foreach($oParam->dataEtapas as $dataEtapa) {

        $oIdadeEtapa          = new IdadeEtapa(new Etapa($dataEtapa->codigo));
        $intervalIdadeInicial = new DBInterval();
        $intervalIdadeFinal   = new DBInterval();

        // Idade inicial
        $intervalIdadeInicial->setMonths($dataEtapa->idade_inicial->mes);
        $intervalIdadeInicial->setYear($dataEtapa->idade_inicial->ano);
        $oIdadeEtapa->setIdadeInicial($intervalIdadeInicial);

        // Idade final
        $intervalIdadeFinal->setMonths($dataEtapa->idade_final->mes);
        $intervalIdadeFinal->setYear($dataEtapa->idade_final->ano);
        $oIdadeEtapa->setIdadeFinal($intervalIdadeFinal);

        $oIdadeEtapa->salvar();
      }

      $oRetorno->sMensagem = _M(MENSAGEM_IDADE_ETAPA.'dados_salvos');

      break;
  }

  db_fim_transacao(false);

} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus   = 2;
  $oRetorno->sMensagem = urlencode($eErro->getMessage());
  $oRetorno->erro      = true;
}

$oRetorno->erro = $oRetorno->iStatus == 2;
echo $oJson->encode($oRetorno);