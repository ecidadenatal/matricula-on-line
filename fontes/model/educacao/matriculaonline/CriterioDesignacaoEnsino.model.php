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

class CriterioDesignacaoEnsino {


  const ARQUIVO_MENSAGEM = "educacao.matriculaonline.CriterioDesignacaoEnsino.";
  /**
   * Ensino
   * @var Ensino
   */
  private $oEnsino;

  /**
   * Ordem dos criterios de Ordenacao
   * @var TipoCriterioDesignacao[]
   */
  private $aOrdens = array();

  /**
   * Instancia as ordens do criterio
   * @param Ensino $oEnsino
   */
  public function __construct(Ensino $oEnsino) {
    $this->oEnsino  = $oEnsino;
  }

  /**
   * Retorna a Ordenacao do Criterio
   */
  public function getOrdenacaoCriterio() {

    if (count($this->aOrdens) == 0) {

      $oDaoCriterioEnsino  = new cl_criteriosdesignacaoensino;
      $sWhere              = " mo17_ensino = {$this->oEnsino->getCodigo()} ";
      $sCampos             = " mo17_criteriosdesignacao, mo17_ordem, mo16_descricao ";
      $sSqlCriteriosEnsino = $oDaoCriterioEnsino->sql_query(null, $sCampos, "mo17_ordem", $sWhere);
      $rsCriterios         = db_query($sSqlCriteriosEnsino);
      if (!$rsCriterios) {
        throw new DBException("Erro ao pesquisar dados do ordenação dos critérios de designação");
      }
      $iTotalCriterios = pg_num_rows($rsCriterios);
      for ($iCriterio = 0; $iCriterio < $iTotalCriterios; $iCriterio++) {

        $oDadosCriterio      = db_utils::fieldsMemory($rsCriterios, $iCriterio);
        $oCriterioDesignacao = new TipoCriterioDesignacao();
        $oCriterioDesignacao->setCodigo($oDadosCriterio->mo17_criteriosdesignacao);
        $oCriterioDesignacao->setDescricao($oDadosCriterio->mo16_descricao);
        $this->aOrdens[$oDadosCriterio->mo17_ordem] = $oCriterioDesignacao;
      }
    }
    return $this->aOrdens;
  }

  public function setOrdenacaoCriterio(TipoCriterioDesignacao $oCriterio, $iOrdem) {
    $this->aOrdens[$iOrdem] = $oCriterio;
  }

  /**
   * persiste os dados do ensino
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Sem transação com o banco de dados");
    }

    if (count($this->aOrdens) == 0) {
      throw new BusinessException(_M(self::ARQUIVO_MENSAGEM."ordenacao_nao_informada"));
    }
    $this->removerDados();
    $oDaoCriterioEnsino = new cl_criteriosdesignacaoensino;
    foreach ($this->getOrdenacaoCriterio() as $iOrdem => $oCriterio) {

      $oDaoCriterioEnsino->mo17_sequencial          = null;
      $oDaoCriterioEnsino->mo17_ensino              = $this->oEnsino->getCodigo();
      $oDaoCriterioEnsino->mo17_ordem               = $iOrdem;
      $oDaoCriterioEnsino->mo17_criteriosdesignacao = $oCriterio->getCodigo();
      $oDaoCriterioEnsino->incluir(null);

      if ($oDaoCriterioEnsino->erro_status == 0) {
        throw new BusinessException(_M(self::ARQUIVO_MENSAGEM."erro_incluir_ordenacao_criterio"));
      }
    }
  }

  /**
   * Remove os dados da
   * @throws BusinessException
   */
  private function removerDados() {

    $oDaoCriterioEnsino = new cl_criteriosdesignacaoensino;
    $oDaoCriterioEnsino->excluir(null, "mo17_ensino = {$this->oEnsino->getCodigo()}");
    if ($oDaoCriterioEnsino->erro_status == 0) {
      throw new BusinessException(_M(self::ARQUIVO_MENSAGEM."erro_remover_ordenacao_criterio"));
    }
  }
}