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
define( 'MENSAGENS_FASE', 'educacao.matriculaonline.Fase.' );
/**
 * Classe para controle de ações referentes a tabela fase
 * @package    educacao
 * @subpackage matriculaonline
 * @author     Fábio Esteves <fabio.esteves@dbseller.com.br>
 */
class Fase {

  /**
   * Código da fase
   * @var integer
   */
  private $iCodigo;

  /**
   * Descrição da fase
   * @var string
   */
  private $sDescricao;

  /**
   * Ano base da fase
   * @var integer
   */
  private $iAno;

  /**
   * Data de início da fase
   * @var DBDate
   */
  private $oDataInicio;

  /**
   * Data de fim da fase
   * @var DBDate
   */
  private $oDataFim;

  /**
   * Ciclo utilizado pela fase
   * @var Ciclo
   */
  private $oCiclo;

  /**
   * Vagas
   * @var Vagas[]
   */
  private $aVagas = array();

  /**
   * Armazena se a fase já foi processada
   * @var boolean
   */
  private $lProcessada = false;

  /**
   * Data utilizada como base para cálculo da idade
   * @var DBDate
   */
  private $oDataCorte;

  public function __construct( $iCodigo = null ) {

    if( empty( $iCodigo ) ) {
      return null;
    }

    $oDaoFase = new cl_fase();
    $sSqlFase = $oDaoFase->sql_query_file( $iCodigo );
    $rsFase   = db_query( $sSqlFase );

    if( !$rsFase ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();
      throw new DBException( _M( MENSAGENS_FASE . 'erro_buscar_fase', $oErro ) );
    }

    if( pg_num_rows( $rsFase ) == 0 ) {

      $oErro          = new stdClass();
      $oErro->iCodigo = $iCodigo;
      throw new BusinessException( _M( MENSAGENS_FASE . 'fase_nao_encontrada', $oErro ) );
    }

    $oDadosFase        = db_utils::fieldsMemory( $rsFase, 0 );
    $this->iCodigo     = $iCodigo;
    $this->sDescricao  = $oDadosFase->mo04_desc;
    $this->iAno        = $oDadosFase->mo04_anousu;
    $this->lProcessada = $oDadosFase->mo04_processada == 't';
    $this->oDataInicio = new DBDate( $oDadosFase->mo04_dtini );
    $this->oDataFim    = new DBDate( $oDadosFase->mo04_dtfim );
    $this->oCiclo      = new Ciclo( $oDadosFase->mo04_ciclo );
    $this->oDataCorte  = new DBDate( $oDadosFase->mo04_datacorte );
  }

  /**
   * Retorna o código da fase
   * @return integer
   */
  public function getCodigo(){
    return $this->iCodigo;
  }

  /**
   * Retorna o ano base da fase
   * @return integer
   */
  public function getAno() {
    return $this->iAno;
  }

  /**
   * Seta o ano base da fase
   * @param integer $iAno
   */
  public function setAno( $iAno ) {
    $this->iAno = $iAno;
  }

  /**
   * Retorna uma instância do ciclo utilizado pela fase
   * @return Ciclo
   */
  public function getCiclo(){
    return $this->oCiclo;
  }

  /**
   * Seta a instância do ciclo da fase
   * @param Ciclo $oCiclo
   */
  public function setCiclo( Ciclo $oCiclo ){
    $this->oCiclo = $oCiclo;
  }

  /**
   * Retorna a data de fim da fase
   * @return DBDate
   */
  public function getDataFim() {
    return $this->oDataFim;
  }

  /**
   * Seta a data de fim da fase
   * @param DBDate $oDataFim
   */
  public function setDataFim( DBDate $oDataFim ) {
    $this->oDataFim = $oDataFim;
  }

  /**
   * Retorna a data de início da fase
   * @return DBDate
   */
  public function getDataInicio() {
    return $this->oDataInicio;
  }

  /**
   * Seta a data de início da fase
   * @param DBDate $oDataInicio
   */
  public function setDataInicio( DBDate $oDataInicio ) {
    $this->oDataInicio = $oDataInicio;
  }

  /**
   * Retorna a descrição da fase
   * @return string
   */
  public function getDescricao() {
    return $this->sDescricao;
  }

  /**
   * Seta a descrição da fase
   * @param string $sDescricao
   */
  public function setDescricao( $sDescricao ) {
    $this->sDescricao = $sDescricao;
  }

  /**
   * Retorna a data de corte da fase
   * @return DBDate
   */
  public function getDataCorte() {
    return $this->oDataCorte;
  }

  /**
   * Retorna as vagas vinculadas a fase
   * @return Vagas[] aVagas
   */
  public function vagasNaFase() {

    if ( count($this->aVagas) == 0) {
      $this->aVagas = VagasRepository::getByFase( $this->iCodigo );
    }

    return $this->aVagas;
  }

  /**
   * Retorna se a fase já foi processada
   * @return boolean
   */
  public function isProcessada() {
    return $this->lProcessada;
  }

  /**
   * Define se a fase já foi processada
   * @param boolean $lProcessada
   */
  public function setProcessada( $lProcessada ) {
    $this->lProcessada = $lProcessada;
  }

  /**
   * Retorna os ensinos da Fase, podendo ser retornada apenas ensinos existentes em determinada escola
   * @param Escola $oEscola
   * @return Ensino[]
   * @throws DBException
   */
  public function getEnsinos( $oEscola = null ) {

    $aEnsinos   = array();
    $sWhereFase = "mo04_codigo = {$this->iCodigo}";

    if( $oEscola != null && $oEscola instanceof Escola ) {
      $sWhereFase .= " AND ed71_i_escola = {$oEscola->getCodigo()}";
    }

    $oDaoFase = new cl_fase();
    $sSqlFase = $oDaoFase->sql_query_ensino_escola( "distinct ed10_i_codigo", null, $sWhereFase );
    $rsFase   = db_query( $sSqlFase );

    if( !$rsFase ) {
      throw new DBException( _M( MENSAGENS_FASE . "erro_buscar_ensinos" ) );
    }

    if( pg_num_rows( $rsFase ) > 0 ) {

      for( $iContador = 0; $iContador < pg_num_rows( $rsFase ); $iContador++ ) {

        $iEnsino    = db_utils::fieldsMemory( $rsFase, $iContador )->ed10_i_codigo;
        $aEnsinos[] = EnsinoRepository::getEnsinoByCodigo( $iEnsino );
      }
    }

    return $aEnsinos;
  }

  /**
   * Salva a Fase
   */
  public function salvar() {

    $oDaoFase                  = new cl_fase();
    $oDaoFase->mo04_codigo     = $this->iCodigo;
    $oDaoFase->mo04_desc       = $this->sDescricao;
    $oDaoFase->mo04_anousu     = $this->iAno;
    $oDaoFase->mo04_dtini      = $this->oDataInicio->getDate();
    $oDaoFase->mo04_dtfim      = $this->oDataFim->getDate();
    $oDaoFase->mo04_ciclo      = $this->oCiclo->getCodigo();
    $oDaoFase->mo04_processada = $this->lProcessada ? 'true' : 'false';
    $oDaoFase->mo04_datacorte  = $this->oDataCorte->getDate();

    if ( !empty($this->iCodigo) ) {
      $oDaoFase->alterar($this->iCodigo);
    } else {
      $oDaoFase->incluir(null);
      $this->iCodigo = $oDaoFase->mo04_codigo;
    }

    $oMsgErro = new stdClass();
    if ($oDaoFase->erro_status == 0) {

      $oMsgErro->sErro = $oDaoFase->erro_msg;
      throw new DBException( _M(MENSAGENS_FASE . "erro_salvar", $oMsgErro) );
    }

  }

  public function processar() {

    $oDaoInscritos = new cl_mobase();

    foreach ($this->getEnsinos() as $oEnsino) {

      $aWhere   = array();
      $aWhere[] = ' mo04_processada = false ';
      $aWhere[] = " mo12_fase = {$this->iCodigo} ";
      $aWhere[] = " ed10_i_codigo = {$oEnsino->getCodigo()} ";

      $oCriterioEnsino = new CriterioDesignacaoEnsino($oEnsino);

      $aOrdem    = array();
      $aOrdem[]  = " mo01_serie ";
      $aCampos   = array();
      $aCampos[] = " mo01_codigo ";
      $aCampos[] = " mo01_serie ";
      $aCampos[] = " mo02_escola ";
      $aCampos[] = " mo03_turno ";
      $aCampos[] = " mo03_opcao ";
      $aCampos[] = " mo03_codigo ";

      foreach ($oCriterioEnsino->getOrdenacaoCriterio() as $oTipoCriterioDesignacao ) {

        $aCampos[] = $oTipoCriterioDesignacao->getRegraCriterio()->getCampo();
        $aOrdem[]  = $oTipoCriterioDesignacao->getRegraCriterio()->getOrdem();
      }

      $aOrdem[] = " mo03_opcao ";
      $aOrdem[] = " mo01_codigo ";
      $sWhere   = implode(" and ", $aWhere);
      $sCampos  = implode(", ", $aCampos );
      $sOrdem   = implode(", ", $aOrdem );

      $sSqlInscritos = $oDaoInscritos->sql_query_inscritos(null, $sCampos, $sOrdem, $sWhere);
      $rsInscritos   = db_query($sSqlInscritos);

      if (!$rsInscritos) {

        $oMsgErro        = new stdClass();
        $oMsgErro->sErro = pg_last_error();
        throw new DBException(  _M( MENSAGENS_FASE . "erro_buscar_inscritos", $oMsgErro ) );
      }

      $aInscritos = array();

      $iLinhas = pg_num_rows($rsInscritos);
      for ($i=0; $i < $iLinhas; $i++) {

        $oDados = db_utils::fieldsMemory( $rsInscritos, $i);
        $aInscritos[$oDados->mo01_codigo][] = $oDados;
      }

      foreach ( $aInscritos as $aDadosInscrito ) {

        foreach ( $aDadosInscrito as $oInscrito ) {

          $lTemVaga = $this->validaVagaInscricao($oInscrito);
          if ($lTemVaga) {
            break;
          }
        }
      }
    }

    $this->atualizaVagas();
    $this->setProcessada(true);
    $this->salvar();
  }

  /**
   * Atualiza as vagas da fase
   */
  private function atualizaVagas() {

    foreach ( $this->vagasNaFase() as $oVaga ) {
      $oVaga->salvar();
    }
  }

  /**
   * Valida se tem vaga para opção do aluno
   * se tem já designa o aluno
   * @param  stdClass $oInscrito
   * @return boolean
   */
  private function validaVagaInscricao($oInscrito) {

    foreach ($this->vagasNaFase() as $oVaga) {

      if ( ($oVaga->getEscola() == $oInscrito->mo02_escola) &&
           ($oVaga->getSerie() == $oInscrito->mo01_serie)   &&
           ($oVaga->getTurno() == $oInscrito->mo03_turno)   &&
           ($oVaga->getSaldoVagas() > 0) ) {

        $this->designar( $oInscrito );
        $oVaga->setSaldoVagas( $oVaga->getSaldoVagas() - 1 );
        return true;
      }
    }

    return false;
  }

  /**
   * Designa o incrito para uma escola de sua escolha
   * @param  stdClass $oInscrito dados do inscrito
   * @throws DBException
   */
  private function designar( $oInscrito ) {

    $oDaoAlocados                    = new cl_alocados();
    $oDaoAlocados->mo13_codigo       = null;
    $oDaoAlocados->mo13_base         = $oInscrito->mo01_codigo;
    $oDaoAlocados->mo13_fase         = $this->iCodigo;
    $oDaoAlocados->mo13_baseescturno = $oInscrito->mo03_codigo;
    $oDaoAlocados->incluir(null);

    $oMsgErro = new stdClass();
    if ($oDaoAlocados->erro_status == 0) {

      $oMsgErro->sErro = $oDaoAlocados->erro_msg;
      throw new DBException( _M(MENSAGENS_FASE . "erro_alocar_inscrito", $oMsgErro) );
    }

  }

  /**
   * Lista os inscritos que não foram designados para as vagas da Fase.
   * @param integer $iEtapa
   * @param integer $iEscola
   * @return array
   * @throws DBException
   * @throws Exception
   */
  public function listaNaoDesignados( $iEtapa = null, $iEscola = null ) {

    $aNaoDesignados = array();

    foreach ($this->getEnsinos() as $oEnsino) {

      $aWhere   = array();
      $aWhere[] = ' mo04_processada = true ';
      $aWhere[] = " mo12_fase = {$this->iCodigo} ";
      $aWhere[] = " ed10_i_codigo = {$oEnsino->getCodigo()} ";

      if ( !empty($iEtapa) ) {
        $aWhere[] = " mo01_serie = {$iEtapa} ";
      }

      if ( !empty($iEscola) ) {
        $aWhere[] = " mo02_escola = {$iEscola} ";
      }

      $oCriterioEnsino = new CriterioDesignacaoEnsino($oEnsino);

      $aOrdem    = array();
      $aOrdem[]  = " mo01_serie ";
      $aCampos   = array();
      $aCampos[] = " mo01_codigo ";
      $aCampos[] = " mo01_nome ";
      $aCampos[] = " mo01_serie ";
      $aCampos[] = " ed11_i_ensino ";
      $aCampos[] = " ed11_i_sequencia ";
      $aCampos[] = " ed11_c_descr as nome_etapa";
      $aCampos[] = " to_char(mo01_dtnasc, 'DD/mm/YYYY') as data_nascimento";
      $aCampos[] = " to_char(mo01_dtnasc, 'DD/mm/YYYY') as data_nascimento";
      $aCampos[] = " mo01_telcel as celular_aluno ";
      $aCampos[] = " mo01_telresp as telefone_responsavel ";

      foreach ($oCriterioEnsino->getOrdenacaoCriterio() as $oTipoCriterioDesignacao ) {

        $aCampos[] = $oTipoCriterioDesignacao->getRegraCriterio()->getCampo();
        $aOrdem[]  = $oTipoCriterioDesignacao->getRegraCriterio()->getOrdem();
      }

      $aOrdem[] = " mo03_opcao ";
      $aOrdem[] = " mo01_codigo ";

      // Listando alunos que não estão na tabela de alocados.
      $aWhere[] = " not exists (select 1 from plugins.alocados where plugins.alocados.mo13_base = plugins.basefase.mo12_base and plugins.alocados.mo13_fase = {$this->getCodigo()}) ";

      $sWhere   = implode(" and ", $aWhere);
      $sCampos  = implode(", ", $aCampos );
      $sOrdem   = implode(", ", $aOrdem );

      $oDaoInscritos     = new cl_mobase();
      $sSqlNaoDesignados = $oDaoInscritos->sql_query_inscritos(null, $sCampos, $sOrdem, $sWhere);
      $rsNaoDesignados   = db_query( $sSqlNaoDesignados );

      if ( !$rsNaoDesignados ) {

        $oMsgErro        = new stdClass();
        $oMsgErro->sErro = pg_last_error();
        throw new DBException( _M(MENSAGENS_FASE . "erro_buscar_nao_designados", $oMsgErro) );
      }

      $iLinhas = pg_num_rows($rsNaoDesignados);
      for ( $i = 0; $i < $iLinhas; $i++ ) {

        $oDados     = db_utils::fieldsMemory( $rsNaoDesignados, $i);
        $sHashSerie = "{$oDados->ed11_i_ensino}{$oDados->ed11_i_sequencia}";

        $aNaoDesignados[$sHashSerie][$oDados->mo01_codigo] = $oDados;
      }
    }

    // Ordenando os registros por Ensino e Sequencia da Etapa
    ksort($aNaoDesignados, SORT_NUMERIC);

    return $aNaoDesignados;
  }
}