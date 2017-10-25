<?php
/**
 * E-cidade Software Publico para Gestão Municipal
 *   Copyright (C) 2015 DBSeller Serviços de Informática Ltda
 *                          www.dbseller.com.br
 *                          e-cidade@dbseller.com.br
 *   Este programa é software livre; você pode redistribuí-lo e/ou
 *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme
 *   publicada pela Free Software Foundation; tanto a versão 2 da
 *   Licença como (a seu critério) qualquer versão mais nova.
 *   Este programa e distribuído na expectativa de ser útil, mas SEM
 *   QUALQUER GARANTIA; sem mesmo a garantia implícita de
 *   COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
 *   PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
 *   detalhes.
 *   Você deve ter recebido uma cópia da Licença Pública Geral GNU
 *   junto com este programa; se não, escreva para a Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307, USA.
 *   Cópia da licença no diretório licenca/licenca_en.txt
 *                                 licenca/licenca_pt.txt
 */


/**
 * Controle de Idade por Etapa para a matricula online dos alunos
 * Class IdadeEtapa
 */
class IdadeEtapa {

  private $iCodigo;

  /**
   * @var Etapa
   */
  private $oEtapa;

  /**
   * @var DBInterval
   */
  private $oIdadeInicial;

  /**
   * @var DBInterval
   */
  private $oIdadeFinal;

  const MENSAGEMS = "educacao.matriculaonline.IdadeEtapa.";
  /**
   * Instância uma nova idade/Etaoa
   *
   * @paratoucm $iCodigo
   * @throws BusinessException
   */
  public function __construct(Etapa $oEtapa) {

    if (!empty($oEtapa)) {

      $daoIdadeEtapa  = new cl_idadeetapa();
      $sSqlIdadeEtapa = $daoIdadeEtapa->sql_query_file(null, "*", null, "mo15_etapa = {$oEtapa->getCodigo()}");
      $rsIdadeEtapa   = db_query($sSqlIdadeEtapa);
      if (!$rsIdadeEtapa) {
        throw new BusinessException(self::MENSAGEMS."erro_pesquisa_etapas", (object) array("etapa" => $oEtapa->getNome()));
      }

      $oDadosIdadeEtapa = db_utils::fieldsMemory($rsIdadeEtapa, 0);
      $this->setCodigo($oDadosIdadeEtapa->mo15_sequencial);
      $this->setIdadeInicial(new DBInterval($oDadosIdadeEtapa->mo15_idadeinicial));
      $this->setIdadeFinal(new DBInterval($oDadosIdadeEtapa->mo15_idadefinal));
      $this->setEtapa($oEtapa);
    }
  }

  /**
   * @return mixed
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * @param mixed $iCodigo
   */
  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  /**
   * @return Etapa
   */
  public function getEtapa() {
    return $this->oEtapa;
  }

  /**
   * @param Etapa $oEtapa
   */
  public function setEtapa(Etapa $oEtapa) {
    $this->oEtapa = $oEtapa;
  }

  /**
   * @return DBInterval
   */
  public function getIdadeInicial() {
    return $this->oIdadeInicial;
  }

  /**
   * @param DBInterval $oIdadeInicial
   */
  public function setIdadeInicial(DBInterval $oIdadeInicial) {
    $this->oIdadeInicial = $oIdadeInicial;
  }

  /**
   * @return DBInterval
   */
  public function getIdadeFinal() {
    return $this->oIdadeFinal;
  }

  /**
   * @param DBInterval $oIdadeFinal
   */
  public function setIdadeFinal(DBInterval $oIdadeFinal) {
    $this->oIdadeFinal = $oIdadeFinal;
  }

  /**
   * Persistência no banco
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException( _M( self::MENSAGEMS . "sem_transacao" ) );
    }

    if (empty($this->oEtapa)) {
      throw new BusinessException( _M( self::MENSAGEMS . "informe_etapa" ) );
    }

    if (empty($this->oIdadeInicial)) {
      throw new BusinessException( _M( self::MENSAGEMS . "informe_idade_inicial" ) );
    }

    if (empty($this->oIdadeFinal)) {
      throw new BusinessException( _M( self::MENSAGEMS . "informe_idade_final" ) );
    }

    if ( $this->oIdadeInicial->greaterThan( $this->oIdadeFinal ) ) {

      $oMsgEtapa         = new stdClass();
      $oMsgEtapa->sEtapa = $this->getEtapa()->getNome();
      throw new BusinessException( _M( self::MENSAGEMS . "idade_inicial_maior_idade_final", $oMsgEtapa));
    }

    $oDAOIdadeEtapa                    = new cl_idadeetapa();
    $oDAOIdadeEtapa->mo15_etapa        = $this->getEtapa()->getCodigo();
    $oDAOIdadeEtapa->mo15_idadeinicial = $this->getIdadeInicial()->getInterval();
    $oDAOIdadeEtapa->mo15_idadefinal   = $this->getIdadeFinal()->getInterval();
    $oDAOIdadeEtapa->mo15_sequencial   = $this->getCodigo();
    if (empty($this->iCodigo)) {

      $oDAOIdadeEtapa->incluir(null);
      $this->setCodigo($oDAOIdadeEtapa->mo15_sequencial);

    } else {
      $oDAOIdadeEtapa->alterar($this->getCodigo());
    }

    if ($oDAOIdadeEtapa->erro_status == 0) {
      throw new BusinessException( _M( self::MENSAGEMS . "erro_salvar_vinculo" ) );
    }
  }
}