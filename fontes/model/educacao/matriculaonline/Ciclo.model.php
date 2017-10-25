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

define( 'MENSAGENS_CICLOS_MODEL', 'educacao.matriculaonline.Ciclo.' );
/**
 * Classe para controle das informações referente aos Ciclos do Matrícula Online
 * @package    educacao
 * @subpackage matriculaonline
 * @author     Fábio Esteves <fabio.esteves@dbseller.com.br>
 */
class Ciclo {

  private $iCodigo = null;
  private $lStatus;
  private $oDataCadastro;
  private $sDescricao;
  private $sSigla;
  private $lEja;

  public function __construct( $iCodigo = null ) {

    if( empty( $iCodigo ) ) {
      return null;
    }

    $oDaoCiclos = new cl_ciclos();
    $sSqlCiclos = $oDaoCiclos->sql_query_file( $iCodigo );
    $rsCiclos   = db_query( $sSqlCiclos );

    if( !$rsCiclos ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();
      throw new DBException( _M( MENSAGENS_CICLOS_MODEL . 'erro_buscar_ciclo', $oErro ) );
    }

    if( pg_num_rows( $rsCiclos ) > 0 ) {

      $oDadosCiclo = db_utils::fieldsMemory( $rsCiclos, 0 );

      $this->iCodigo       = $iCodigo;
      $this->lStatus       = $oDaoCiclos->mo09_status == 't' ? true : false;
      $this->oDataCadastro = new DBDate( $oDadosCiclo->mo09_dtcad );
      $this->sDescricao    = $oDadosCiclo->mo09_descricao;
      $this->sSigla        = $oDadosCiclo->mo09_sigla;
      $this->lEja          = $oDadosCiclo->mo09_eja == 't' ? true : false;
    }
  }

  /**
   * Retornar o código
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * Retorna se o ciclo é referente a EJA
   * @return boolean
   */
  public function isEja() {
    return $this->lEja;
  }

  /**
   * Seta se o ciclo é referente a EJA
   * @param boolean $lEja
   */
  public function setEja( $lEja ) {
    $this->lEja = $lEja;
  }

  /**
   * Retorna se o ciclo está ativo
   * @return boolean
   */
  public function isStatus() {
    return $this->lStatus;
  }

  /**
   * Seta o status do ciclo
   * @param boolean $lStatus
   */
  public function setStatus( $lStatus ) {
    $this->lStatus = $lStatus;
  }

  /**
   * Retorna a data de cadastro do ciclo
   * @return DBDate
   */
  public function getDataCadastro() {
    return $this->oDataCadastro;
  }

  /**
   * Seta a data de cadastro do ciclo
   * @param DBDate $oDataCadastro
   */
  public function setDataCadastro( DBDate $oDataCadastro ) {
    $this->oDataCadastro = $oDataCadastro;
  }

  /**
   * Retorna a descrição do ciclo
   * @return string
   */
  public function getDescricao() {
    return $this->sDescricao;
  }

  /**
   * Seta a descrição do ciclo
   * @param string $sDescricao
   */
  public function setDescricao( $sDescricao ) {
    $this->sDescricao = $sDescricao;
  }

  /**
   * Retorna a sigla do ciclo
   * @return string
   */
  public function getSigla() {
    return $this->sSigla;
  }

  /**
   * Seta a sigla do ciclo
   * @param string $sSigla
   */
  public function setSigla( $sSigla ) {
    $this->sSigla = $sSigla;
  }

  /**
   * Retorna uma coleção dos ensinos vinculados ao Ciclo
   * @return Ensino[]
   * @throws BusinessException
   * @throws DBException
   */
  public function getEnsinosVinculados() {

    if( $this->iCodigo == null ) {
      throw new BusinessException( _M( MENSAGENS_CICLOS_MODEL . 'codigo_nao_informado' ) );
    }

    $aEnsinos         = array();
    $oDaoCiclosEnsino = new cl_ciclosensino();
    $sSqlCiclosEnsino = $oDaoCiclosEnsino->sql_query_file( null, "mo14_ensino", null, "mo14_ciclo = {$this->iCodigo}" );
    $rsCiclosEnsino   = db_query( $sSqlCiclosEnsino );

    if( !$rsCiclosEnsino ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();
      throw new DBException( _M( MENSAGENS_CICLOS_MODEL . 'erro_buscar_ciclos_ensino', $oErro ) );
    }

    for( $iContador = 0; $iContador < pg_num_rows( $rsCiclosEnsino ); $iContador++ ) {

      $iEnsino = db_utils::fieldsMemory( $rsCiclosEnsino, $iContador )->mo14_ensino;
      $oEnsino = EnsinoRepository::getEnsinoByCodigo( $iEnsino );

      $aEnsinos[] = $oEnsino;
    }

    return $aEnsinos;
  }

  /**
   * Retorna as fases que utilizam o ciclo
   * @return Fase[]
   * @throws BusinessException
   * @throws DBException
   */
  public function getFasesVinculadas() {

    if( $this->iCodigo == null ) {
      throw new BusinessException( _M( MENSAGENS_CICLOS_MODEL . 'codigo_nao_informado' ) );
    }

    $oDaoFases   = new cl_fase();
    $sWhereFases = "mo04_ciclo = {$this->iCodigo}";
    $sSqlFases   = $oDaoFases->sql_query_file( null, "mo04_codigo", "mo04_codigo", $sWhereFases );
    $rsFases     = db_query( $sSqlFases );

    if( !$rsFases ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();
      throw new DBException( _M( MENSAGENS_CICLOS_MODEL . 'erro_buscar_fases', $oErro ) );
    }

    $aFases = array();

    for( $iContador = 0; $iContador < pg_num_rows( $rsFases ); $iContador++ ) {

      $iFase = db_utils::fieldsMemory( $rsFases, $iContador )->mo04_codigo;
      $oFase = new Fase( $iFase );

      $aFases[] = $oFase;
    }

    return $aFases;
  }

  /**
   * Exclui um ciclo e todos os seus vínculos( ciclosensino / vagas )
   * @throws BusinessException
   * @throws DBException
   */
  public function excluir() {

    if( $this->iCodigo == null ) {
      throw new BusinessException( _M( MENSAGENS_CICLOS_MODEL . 'codigo_nao_informado' ) );
    }

    $oDaoVagas        = new cl_vagas();
    $oDaoFase         = new cl_fase();
    $oDaoCiclosEnsino = new cl_ciclosensino();
    $oDaoCiclos       = new cl_ciclos();

    $sWhereVagasCiclo = "mo04_ciclo = {$this->iCodigo}";
    $sSqlVagasCiclo   = $oDaoVagas->sql_query( null, "mo10_codigo", null, $sWhereVagasCiclo );
    $rsVagasCiclo     = db_query( $sSqlVagasCiclo );

    if( $rsVagasCiclo && pg_num_rows( $rsVagasCiclo ) > 0 ) {

      for( $iContador = 0; $iContador < pg_num_rows( $rsVagasCiclo ); $iContador++ ) {

        $iCodigoVaga = db_utils::fieldsMemory( $rsVagasCiclo, $iContador )->mo10_codigo;
        $oDaoVagas->excluir( null, "mo10_codigo = {$iCodigoVaga}" );

        if( $oDaoVagas->erro_status == 0 ) {
          throw new DBException( $oDaoVagas->erro_msg );
        }
      }
    }

    $oDaoFase->excluir( null, "mo04_ciclo = {$this->iCodigo}" );

    if( $oDaoFase->erro_status == 0 ) {
      throw new DBException( $oDaoFase->erro_msg );
    }

    $oDaoCiclosEnsino->excluir( null, "mo14_ciclo = {$this->iCodigo}" );

    if( $oDaoCiclosEnsino->erro_status == 0 ) {
      throw new DBException( $oDaoCiclosEnsino->erro_msg );
    }

    $oDaoCiclos->excluir( $this->iCodigo );

    if( $oDaoCiclos->erro_status == 0 ) {
      throw new DBException( $oDaoCiclos->erro_msg );
    }
  }
}