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

define('MENSAGEM_CICLOSRPC', 'educacao.matriculaonline.mol4_ciclos_RPC.');

$oJson               = new services_json();
$oParam              = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno            = new stdClass();
$oRetorno->iStatus   = 1;
$oRetorno->sMensagem = '';
$oRetorno->erro      = false;

try {

  db_inicio_transacao();

  switch ($oParam->sExecucao) {

    /**
     * Verifica se o Ciclo selecionado possuí vínculo com algum ensino
     * Validação utilizada para os casos antigos, onde não havia esse vínculo
     */
    case "validarEnsinoVinculadoCiclo":

      if ( !isset($oParam->iCiclo) || empty($oParam->iCiclo)) {
        throw new ParameterException( _M(MENSAGEM_CICLOSRPC . "informe_ciclo") );
      }

      $oDaoCiclosEnsino   = new cl_ciclosensino();
      $sWhereCiclosEnsino = " mo14_ciclo = {$oParam->iCiclo} ";
      $sSqlCiclosEnsino   = $oDaoCiclosEnsino->sql_query_file( null, "mo14_ensino", null, $sWhereCiclosEnsino );
      $rsCiclosEnsino     = db_query( $sSqlCiclosEnsino );

      if ( !$rsCiclosEnsino ) {

        $oErro->sErro = pg_last_error();
        throw new DBException( _M(MENSAGEM_CICLOSRPC . "erro_buscar_vinculo_ciclo_ensino", $oErro) );
      }

      $oRetorno->lPossuiVinculo = false;

      if ( pg_num_rows($rsCiclosEnsino) > 0 ) {
        $oRetorno->lPossuiVinculo = true;
      }

      break;

    /**
     * Retorna uma coleção dos ensinos vinculados a um ciclo
     */
    case 'pesquisaEnsinosVinculadosCiclo':

      if( !isset( $oParam->iCiclo ) || empty( $oParam->iCiclo ) ) {
        throw new ParameterException( _M( MENSAGEM_CICLOSRPC . 'ciclo_nao_informado' ) );
      }

      $oRetorno->aEnsinos = array();
      $oCiclo             = new Ciclo( $oParam->iCiclo );

      foreach( $oCiclo->getEnsinosVinculados() as $oEnsino ) {

        $oDadosEnsino             = new stdClass();
        $oDadosEnsino->iCodigo    = $oEnsino->getCodigo();
        $oDadosEnsino->sDescricao = urlencode( $oEnsino->getNome() );

        $oRetorno->aEnsinos[] = $oDadosEnsino;
      }

      break;

    /**
     * Retorna uma coleção dos ensinos vinculados a uma fase
     */
    case 'pesquisaEnsinosVinculadosFase':

      if( !isset( $oParam->iFase ) || empty( $oParam->iFase ) ) {
        throw new ParameterException( _M( MENSAGEM_CICLOSRPC . 'fase_nao_informada' ) );
      }

      $oRetorno->aEnsinos = array();
      $oFase              = new Fase( $oParam->iFase );
      $aEnsinosVinculados = $oFase->getEnsinos();

      if( isset( $oParam->iEscola ) && !empty( $oParam->iEscola ) ) {

        $oEscola            = EscolaRepository::getEscolaByCodigo( $oParam->iEscola );
        $aEnsinosVinculados = $oFase->getEnsinos( $oEscola );
      }

      foreach( $aEnsinosVinculados as $oEnsino ) {

        $oDadosEnsino             = new stdClass();
        $oDadosEnsino->iCodigo    = $oEnsino->getCodigo();
        $oDadosEnsino->sDescricao = urlencode( $oEnsino->getNome() );

        $oRetorno->aEnsinos[] = $oDadosEnsino;
      }

      break;

    case 'pesquisaVagasVinculadasEnsino':

      $oRetorno->iEnsino                = $oParam->iEnsino;
      $oRetorno->lVagasVinculadasEnsino = false;

      $oDaoVaga    = new cl_vagas();

      $sWhereVaga  = " mo04_ciclo  = {$oParam->iCiclo} ";
      $sWhereVaga .= " AND mo10_ensino = {$oParam->iEnsino} ";
      $sWhereVaga .= " AND mo10_numvagas > 0 ";

      $sSqlVaga    = $oDaoVaga->sql_query(null, '1', null, $sWhereVaga );
      $rsVaga      = $oDaoVaga->sql_record($sSqlVaga);

      if ( $oDaoVaga->numrows > 0 ) {
        $oRetorno->lVagasVinculadasEnsino = true;
      }

      break;

    case 'verificaFasesVinculadas':

      if( !isset( $oParam->iCiclo ) || empty( $oParam->iCiclo ) ) {
        throw new ParameterException( _M( MENSAGEM_CICLOSRPC . 'ciclo_nao_informado' ) );
      }

      $oRetorno->aFases = array();
      $oCiclo           = new Ciclo( $oParam->iCiclo );

      foreach( $oCiclo->getFasesVinculadas() as $oFase ) {

        $oDadosFase              = new stdClass();
        $oDadosFase->iCodigo     = $oFase->getCodigo();
        $oDadosFase->sDescricao  = urlencode( $oFase->getDescricao() );
        $oDadosFase->iAno        = $oFase->getAno();
        $oDadosFase->sDataInicio = $oFase->getDataInicio()->getDate( DBDate::DATA_PTBR );
        $oDadosFase->sDataFim    = $oFase->getDataFim()->getDate( DBDate::DATA_PTBR );

        $oRetorno->aFases[] = $oDadosFase;
      }

      break;

    case 'pesquisaVagasVinculadasCiclo':

      $oRetorno->lVagasVinculadasCiclo = false;
      $oRetorno->lMostra = $oParam->lMostra;

      if ( isset( $oParam->iFase ) && !empty($oParam->iFase)) {

        $oDaoFase    = new cl_fase();
        $sSqlFase    = $oDaoFase->sql_query($oParam->iFase, 'mo04_ciclo');
        $rsFase      = db_query($sSqlFase);

        if ( !$rsFase ) {
          throw new DBException("Erro ao Processar Pesquisa da Fase:".pg_last_error());
        }

        if ( pg_num_rows($rsFase) > 0 ) {

          $oFase = db_utils::fieldsMemory( $rsFase, 0 );
          $oRetorno->iCiclo = $oFase->mo04_ciclo;

          $oDaoVaga    = new cl_vagas();
          $sWhereVaga  = " mo10_fase = {$oParam->iFase} ";
          $sWhereVaga .= " AND mo04_ciclo  = {$oFase->mo04_ciclo} ";
          $sWhereVaga .= " AND mo10_numvagas > 0 ";

          $sSqlVaga    = $oDaoVaga->sql_query(null, '1', null, $sWhereVaga );
          $rsVaga      = db_query($sSqlVaga);

          if ( pg_num_rows($rsVaga) > 0 ) {
            $oRetorno->lVagasVinculadasCiclo = true;
          }
        }
      }

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