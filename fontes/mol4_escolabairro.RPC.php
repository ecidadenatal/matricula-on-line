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

define('MENSAGENS_ESCOLABAIRRO_RPC', 'educacao.matriculaonline.mol4_escolabairro_RPC.');

$oJson               = new services_json();
$oParam              = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno            = new stdClass();
$oRetorno->iStatus   = 1;
$oRetorno->sMensagem = '';
$oRetorno->erro      = false;

try {

  switch( $oParam->sExecuta ) {

    /**
     * Busca os bairros que tenham sido cadastrados para atendimento da escola
     */
    case 'bairrosVinculados':

      if( !isset( $oParam->iEscola ) || empty( $oParam->iEscola ) ) {
        throw new ParameterException( _M( MENSAGENS_ESCOLABAIRRO_RPC . 'escola_nao_informada' ) );
      }

      $oDaoEscolaBairro     = new cl_escbairro();
      $sCamposEscolaBairro  = 'mo08_bairro as codigo_bairro, j13_descr as nome_bairro';
      $sWhereEscolaBairro   = "mo08_escola = {$oParam->iEscola}";
      $sSqlEscolaBairro     = $oDaoEscolaBairro->sql_query_escola_bairro(
                                                                          $sCamposEscolaBairro,
                                                                          "nome_bairro",
                                                                          $sWhereEscolaBairro
                                                                        );
      $rsEscolaBairro = db_query( $sSqlEscolaBairro );

      if( !$rsEscolaBairro ) {

        $oErro        = new stdClass();
        $oErro->sErro = pg_last_error();
        throw new DBException( _M(  MENSAGENS_ESCOLABAIRRO_RPC . 'erro_buscar_bairros', $oErro ) );
      }

      $oRetorno->aBairros = db_utils::getCollectionByRecord( $rsEscolaBairro, false, false, true );

      break;

    /**
     * Salva os bairros atendidos por uma escola
     */
    case 'salvarBairros':

      if( !isset( $oParam->iEscola ) || empty( $oParam->iEscola ) ) {
        throw new ParameterException( _M( MENSAGENS_ESCOLABAIRRO_RPC . 'escola_nao_informada' ) );
      }

      db_inicio_transacao();

      $oDaoEscolaBairro = new cl_escbairro();
      $oDaoEscolaBairro->excluir( null, " mo08_escola = {$oParam->iEscola} " );

      if ( $oDaoEscolaBairro->erro_status == "0" ) {

        $oErro        = new stdClass();
        $oErro->sErro = $oDaoEscolaBairro->erro_msg;
        throw new DBException( _M( MENSAGENS_ESCOLABAIRRO_RPC . 'erro_excluir_bairros' ) );
      }

      foreach( $oParam->aBairros as $iBairro ) {

        /**
         * Instanciada a DAO a cada vez que percorre os bairros, pois a DAOBasica não consegue identificar o campo
         * sequencial.
         * Caso seja identificada perda de performance, quando muitos barros foram adicionadas, alterar para ao invés
         * de instanciar a DAO, setar o campo m08_codigo = null, por exemplo
         */
        $oDaoEscolaBairro              = new cl_escbairro();
        $oDaoEscolaBairro->mo08_escola = $oParam->iEscola;
        $oDaoEscolaBairro->mo08_bairro = $iBairro;
        $oDaoEscolaBairro->incluir( null );

        if ( $oDaoEscolaBairro->erro_status == "0" ) {

          $oErro        = new stdClass();
          $oErro->sErro = $oDaoEscolaBairro->erro_msg;
          throw new DBException( _M( MENSAGENS_ESCOLABAIRRO_RPC . 'erro_salvar_bairros' ) );
        }
      }

      $oRetorno->sMensagem = urlencode( _M( MENSAGENS_ESCOLABAIRRO_RPC . 'dados_sucesso' ) );

      db_fim_transacao();

      break;
  }
} catch (Exception $eErro){

  db_fim_transacao(true);

  $oRetorno->iStatus   = 2;
  $oRetorno->sMensagem = urlencode($eErro->getMessage());
  $oRetorno->erro      = true;
}

echo $oJson->encode($oRetorno);