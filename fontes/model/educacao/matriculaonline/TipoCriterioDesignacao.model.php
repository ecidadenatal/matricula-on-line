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
 * Classe para controle do Criterio de Ordenacao
 * Class TipoCriterioDesignacao
 */
class TipoCriterioDesignacao {

  /**
   * Código do tipo de ordenacao
   * @var integer
   */
  private $iCodigo;

  /**
   * Descricao do criterio
   * @var string
   */
  private $sDescricao;

  /**
   * Regra aplicada ao criterio
   * @var CriterioDesignacaoRegra
   */
  private $oRegra;


  /**
   * Instancia um tipo de Criterio de ordenacao
   */
  public function __construct() {}

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
   * @return mixed
   */
  public function getDescricao() {
    return $this->sDescricao;
  }

  /**
   * @param mixed $sDescricao
   */
  public function setDescricao($sDescricao) {
    $this->sDescricao = $sDescricao;
  }

  /**
   * @return CriterioBolsaFamilia|CriterioDeficiencia|CriterioDesignacaoRegra|CriterioIdadeMaior|CriterioIdadeMenor|CriterioRedeOrigem|CriterioResponsavelTrabalhador
   * @throws Exception
   */
  public function getRegraCriterio() {

    switch ($this->iCodigo) {

      case 1:
        $this->oRegra = new CriterioDeficiencia();
        break;
      case 2:
        $this->oRegra = new CriterioBolsaFamilia();
        break;
      case 3:
        $this->oRegra = new CriterioIdadeMaior();
        break;
      case 4:
        $this->oRegra = new CriterioResponsavelTrabalhador();
        break;
      case 5:
        $this->oRegra = new CriterioIdadeMenor();
        break;
      case 6:
        $this->oRegra = new CriterioRedeOrigem();
        break;
      default:
        throw new Exception("Regra de designação não encontrada.");
        break;
    }
    return $this->oRegra;
  }

}