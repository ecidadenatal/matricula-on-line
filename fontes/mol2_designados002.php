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
require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_utils.php");
require_once modification("libs/db_conecta_plugin.php");
require_once modification("libs/db_sessoes.php");
require_once modification("fpdf151/pdf.php");
require_once modification("dbforms/db_funcoes.php");

$oGet = db_utils::postMemory($_GET);

define('MSG_MOL2DESIGNADOS002', 'educacao.matriculaonline.mol2_designados002.');
$aDadosRelatorio = array();

try {

  if ( empty($oGet->iFase) ) {
    throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "fase_nao_informada" ) );
  }

  $oFase                = new Fase($oGet->iFase);
  $oEscola              = null;
  $oEtapa               = null;
  $aParametrosCabecalho = array();

  if ( is_null($oFase->getCodigo()) ) {
    throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "fase_nao_encontrada" ) );
  }

  $head1 = "Relatório de Candidatos Designados";
  $head3 = "Fase: ". substr($oFase->getDescricao(), 0, 25);

  if ( !empty($oGet->iEtapa) ) {

    $oEtapa = EtapaRepository::getEtapaByCodigo($oGet->iEtapa);

    if ( is_null($oEtapa->getCodigo()) ) {
      throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "etapa_nao_encontrada" ) );
    }

    $aParametrosCabecalho[] = "Etapa: {$oEtapa->getNome()}";
  }

  if ( !empty($oGet->iEscola) ) {

    $oEscola = EscolaRepository::getEscolaByCodigo($oGet->iEscola);

    if ( is_null($oEscola->getCodigo()) ) {
      throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "escola_nao_encontrada" ) );
    }

    $aParametrosCabecalho[] = "Escola: ". substr($oEscola->getNome(), 0, 25);
  }


  if ($oGet->sModelo == 'A') {
    $aParametrosCabecalho[] = "Ordem: " . ($oGet->sOrdem == 'A' ? 'Alfabética' : 'Designação');
  }

  $aParametrosCabecalho[] = "Modelo: " . ($oGet->sModelo == 'A' ? 'Analítico' : 'Sintético');

  $head4 = implode("\n", $aParametrosCabecalho);

  /**
   * Valida conforme modelo para organizar os dados
   */
  if ( $oGet->sModelo == 'A')  {

    $sOrdem = " mo01_nome ";
    if ( $oGet->sOrdem == 'D' ) {
      $sOrdem = " mo13_codigo ";
    }
    $aDadosRelatorio = buscaDadosAnalitico($oFase, $oEscola, $oEtapa, $sOrdem);
  } else {
    $aDadosRelatorio = buscaDadosSintetico($oFase, $oEscola, $oEtapa);
  }

} catch (Exception $e) {

  $sMsg = $e->getMessage();
  db_redireciona("db_erros.php?fechar=true&db_erro={$sMsg}");
}

/**
 * Organiza os dados dos designados na fase
 * @param Fase        $oFase
 * @param Escola|null $oEscola
 * @param Etapa|null  $oEtapa
 * @param             $sOrdem
 * @return array
 * @throws Exception
 */
function buscaDadosAnalitico(Fase $oFase, Escola $oEscola = null, Etapa $oEtapa = null, $sOrdem) {

  $aVagasFase = VagasRepository::getByFaseFilterEscolaEtapa($oFase, $oEscola, $oEtapa);

  $aVagas = array();
  foreach ($aVagasFase as $oVaga) {

    $sHash = "{$oVaga->getEscola()}#{$oVaga->getSerie()}";
    if ( !array_key_exists($sHash, $aVagas) ) {

      $oEscola = EscolaRepository::getEscolaByCodigo($oVaga->getEscola());
      $oEtapa  = EtapaRepository::getEtapaByCodigo($oVaga->getSerie());

      $oVagas              = new stdClass();
      $oVagas->iFase       = $oVaga->getFase();
      $oVagas->oEscola     = $oEscola;
      $oVagas->oEtapa      = $oEtapa;
      $oVagas->iNumVagas   = 0;
      $oVagas->iSaldoVagas = 0;
      $aVagas[$sHash]      = $oVagas;
    }
    $aVagas[$sHash]->iNumVagas   += $oVaga->getNumVagas();
    $aVagas[$sHash]->iSaldoVagas += $oVaga->getSaldoVagas();
  }

  $aDadosRelatorio = array();
  $lFasePossuiInscritosDesignados = false;
  foreach ($aVagas as $oVaga) {

    $aInscritosDesignadosVaga = getInscritosDesignados($oVaga, $sOrdem);

    if (count($aInscritosDesignadosVaga) == 0) {
      continue;
    }

    $oVaga->aInscritos                               = $aInscritosDesignadosVaga;
    $aDadosRelatorio[$oVaga->oEscola->getCodigo()][] = $oVaga;

    $lFasePossuiInscritosDesignados = true;

  }

  if ( !$lFasePossuiInscritosDesignados ) {
    throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "fase_sem_inscritos_designados" ) );
  }

  return $aDadosRelatorio;
}

/**
 * Retorna os Inscritos e desinados para uma etapa de uma escola
 * @param  Vaga $oVaga
 * @param  string $sOrdem
 * @return stdClass[]
 */
function getInscritosDesignados($oVaga, $sOrdem) {

  $sCampos  = " mo01_codigo,                    ";
  $sCampos .= " mo01_nome     as nome,          ";
  $sCampos .= " mo03_opcao    as opcao,         ";
  $sCampos .= " mo01_telresp  as telefone_resp, ";
  $sCampos .= " mo01_telcel   as telefone,      ";
  $sCampos .= " mo01_dtnasc   as nascimento,    ";
  $sCampos .= " mo03_turno    as turno,         ";
  $sCampos .= " mo02_escola   as escola         ";

  $sWhere  =  "     mo04_codigo = {$oVaga->iFase} ";
  $sWhere .=  " and mo02_escola = {$oVaga->oEscola->getCodigo()} ";
  $sWhere .=  " and mo01_serie  = {$oVaga->oEtapa->getCodigo()} ";

  try {

    $oDaoDesignados = new cl_alocados();
    $sSqlDesignados = $oDaoDesignados->sql_query_dadosaluno(null, $sCampos, $sOrdem, $sWhere);
    $rsDesignados   = pg_query($sSqlDesignados);

    $oMsgErro = new stdClass();
    if (!$rsDesignados) {

      $oMsgErro->sErro = pg_last_error();
      throw new DBException (_M(MSG_MOL2DESIGNADOS002 . "erro_buscar_designados", $oMsgErro));
    }

    if (pg_num_rows($rsDesignados) > 0) {
      return db_utils::getCollectionByRecord($rsDesignados);
    }

  } catch (DBException $e) {
    echo $e->getMessage();
  }

  return array();
}

function buscaDadosSintetico($oFase, $oEscola, $oEtapa) {

  $sCampos  = " ed18_i_codigo, trim(ed18_c_nome)    as escola,       ";
  $sCampos .= " ed10_i_codigo, trim(ed10_c_abrev)   as ensino_abrev, ";
  $sCampos .= " trim(ed10_c_descr)   as ensino,       ";
  $sCampos .= " trim(ed11_c_descr)   as serie,        ";
  $sCampos .= " sum(mo10_numvagas)   as vagas,        ";
  $sCampos .= " sum(mo10_saldovagas) as saldo,        ";
  $sCampos .= " sum(mo10_numvagas) - sum(mo10_saldovagas) as ocupadas ";

  $sWhere   = " mo10_fase = {$oFase->getCodigo()} ";
  $sWhere  .= " and mo10_numvagas > 0 ";

  if ( !empty($oEscola)) {
    $sWhere  .= " and mo10_escola = {$oEscola->getCodigo()} ";
  }
  if ( !empty($oEtapa) ) {
    $sWhere  .= " and mo10_serie = {$oEtapa->getCodigo()} ";
  }

  $sGoupBy  = " group by ed10_i_codigo, ensino, ensino_abrev, ed18_i_codigo, escola, serie, ed10_ordem, ed11_i_sequencia ";
  $sOrder   = " escola, ed10_ordem, ed11_i_sequencia ";

  $oDaoVagas      = new cl_vagas();
  $sSqlDesignados = $oDaoVagas->sql_query_escola_serie_ensino(null, $sCampos, $sOrder, $sWhere . $sGoupBy );
  $rsDesignados   = pg_query($sSqlDesignados);

  $oMsgErro = new stdClass();
  if (!$rsDesignados) {

    $oMsgErro->sErro = pg_last_error();
    throw new DBException (_M(MSG_MOL2DESIGNADOS002 . "erro_buscar_designados", $oMsgErro));
  }
  $aDadosRelatorio = array();
  $iLinhas         = pg_num_rows($rsDesignados);

  if ( $iLinhas == 0 ) {

    throw new Exception( _M( MSG_MOL2DESIGNADOS002 . "fase_sem_inscritos_designados" ) );
  }

  for ($i = 0; $i < $iLinhas; $i++) {

    $oDados = db_utils::fieldsMemory($rsDesignados, $i);
    $aDadosRelatorio[$oDados->ed18_i_codigo][] = $oDados;
  }
  return $aDadosRelatorio;

}

$oPdf = new PDF('P');
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetMargins(8, 10);
$oPdf->SetAutoPageBreak(false, 20);
$oPdf->SetFillColor(240);


if ( $oGet->sModelo == 'A')  {
  imprimeModeloAnalitico($oPdf, $aDadosRelatorio);
} else {
  imprimeModeloSintetico($oPdf, $aDadosRelatorio);
}

function imprimeModeloAnalitico(FPDF $oPdf, $aDadosRelatorio) {

  $iImprimindoEscola = null;

  foreach ($aDadosRelatorio as $iEscola => $aVagasEtapaTurno) {

    $lAdicionaPagina = false;
    if ( (empty($iImprimindoEscola) || $iImprimindoEscola != $iEscola ) ) {
      $lAdicionaPagina = true;
    }

    foreach ($aVagasEtapaTurno as $oVagasEtapaTurno) {

      if ($oPdf->getY() > ($oPdf->h - 20)) {
        $lAdicionaPagina = true;
      }

      imprimeCabecalhoAnalitico($oPdf, $oVagasEtapaTurno, $lAdicionaPagina);
      $lAdicionaPagina = false;
      foreach ($oVagasEtapaTurno->aInscritos as $oInscrito) {

        if ($oPdf->getY() > ($oPdf->h - 20)) {
          imprimeCabecalhoAnalitico($oPdf, $oVagasEtapaTurno, true);
        }
        $sDataNascimento = "";
        if ( !empty($oInscrito->nascimento) ) {

          $oData           = new DBDate($oInscrito->nascimento);
          $sDataNascimento = $oData->convertTo(DBDate::DATA_PTBR);
        }
        $sOpcao     = $oInscrito->opcao . "ª";
        $aTelefones = array();
        if ($oInscrito->telefone) {
          $aTelefones[] = DBString::formatarTelefone($oInscrito->telefone);
        }
        if ( $oInscrito->telefone_resp) {
          $aTelefones[] = DBString::formatarTelefone($oInscrito->telefone_resp);
        }

        $oPdf->SetFont('Arial', '', 7);

        $iPosicaoY          = $oPdf->GetY();
        $aPosicoesMultiCell = array();

        $oPdf->Multicell(90, 4, $oInscrito->nome, 0, 'L');
        $aPosicoesMultiCell[] = $oPdf->GetY();
        $oPdf->SetXY( 98, $iPosicaoY );

        $oPdf->Multicell(20, 4, $sDataNascimento, 0, 'C');
        $aPosicoesMultiCell[] = $oPdf->GetY();
        $oPdf->SetXY( 118, $iPosicaoY );

        $oPdf->Multicell(15, 4, $sOpcao, 0, 'C');
        $aPosicoesMultiCell[] = $oPdf->GetY();
        $oPdf->SetXY( 133, $iPosicaoY );

        $oPdf->Multicell(70, 4, implode(" / ", $aTelefones), 0, 'C');
        $aPosicoesMultiCell[] = $oPdf->GetY();
        $oPdf->SetXY( 203, $iPosicaoY );

        $iMaiorYDefinido = array_reduce($aPosicoesMultiCell, "maior");

        $oPdf->Line(   8,       $iPosicaoY,   8, $iMaiorYDefinido );
        $oPdf->Line(  98,       $iPosicaoY,  98, $iMaiorYDefinido );
        $oPdf->Line( 118,       $iPosicaoY, 118, $iMaiorYDefinido );
        $oPdf->Line( 133,       $iPosicaoY, 133, $iMaiorYDefinido );
        $oPdf->Line( 203,       $iPosicaoY, 203, $iMaiorYDefinido );
        $oPdf->Line(   8, $iMaiorYDefinido, 203, $iMaiorYDefinido );

        $oPdf->SetY( $iMaiorYDefinido );
      }
      $oPdf->ln();
    }
  }
}

function imprimeCabecalhoAnalitico($oPdf, $oVagasEtapaTurno, $lAdicionaPagina){

  $oPdf->SetFont('Arial', 'B', 8);
  if ($lAdicionaPagina) {

    $oPdf->AddPage();
    $oPdf->Cell(193, 4, $oVagasEtapaTurno->oEscola->getNome(), 0, 1);
    $oPdf->Ln(2);
  }

  $oPdf->Cell(100, 4, $oVagasEtapaTurno->oEtapa->getNome(),      0, 0);
  $oPdf->Cell(50,  4, "Vagas: "  . $oVagasEtapaTurno->iNumVagas, 0, 0);
  $oPdf->Cell(43,  4, "Ocupadas: " . ($oVagasEtapaTurno->iNumVagas - $oVagasEtapaTurno->iSaldoVagas), 0, 1);

  $oPdf->Cell(90, 4, "Aluno",      1, 0, 'C', 1);
  $oPdf->Cell(20, 4, "Nascimento", 1, 0, 'C', 1);
  $oPdf->Cell(15, 4, "Opção",      1, 0, 'C', 1);
  $oPdf->Cell(70, 4, "Telefones",  1, 1, 'C', 1);
}

function imprimeModeloSintetico(FPDF $oPdf, $aDadosRelatorio) {

  $lPrimeiraPagina = true;
  foreach ($aDadosRelatorio as $iEscola => $aDadosEscola) {

    $iLinhasEscola = count($aDadosEscola);
    $iAlturaEscola = $iLinhasEscola * 4; // 4 = altura da linha

    // 20 desconto para margim inferior da folha
    if ($lPrimeiraPagina || ($oPdf->getY() > $oPdf->h - (20 + $iAlturaEscola)) ) {

      $oPdf->AddPage();
      $lPrimeiraPagina = false;
    }

    $sEscola = $aDadosEscola[0]->escola;

    $iTotalVagas    = 0;
    $iTotalOcupadas = 0;
    $oPdf->SetFont('Arial', 'B', 8);
    $oPdf->Cell(193, 4, $sEscola,     0, 1, 'L');
    imprimeCabecalhoSintetico($oPdf);
    foreach ($aDadosEscola as $oDados) {

      $oPdf->SetFont('Arial', '', 7);
      $oPdf->Cell(95, 4, $oDados->ensino,       1, 0, 'L');
      $oPdf->Cell(40, 4, $oDados->serie,        1, 0, 'C');
      $oPdf->Cell(30, 4, $oDados->vagas,        1, 0, 'C');
      $oPdf->Cell(30, 4, $oDados->ocupadas,     1, 1, 'C');

      $iTotalVagas    += $oDados->vagas;
      $iTotalOcupadas += $oDados->ocupadas;
    }

    $oPdf->SetFont('Arial', 'B', 8);
    $oPdf->Cell(135, 4, "Total de Vagas",    1, 0, 'R', 1);
    $oPdf->Cell(30,  4, "{$iTotalVagas}",    1, 0, 'C', 1);
    $oPdf->Cell(30,  4, "{$iTotalOcupadas}", 1, 1, 'C', 1);
    $oPdf->ln();
  }
}

function imprimeCabecalhoSintetico ($oPdf) {

  $oPdf->SetFont('Arial', 'B', 8);

  $oPdf->Cell(95, 4, "Ensino",    1, 0, 'C', 1);
  $oPdf->Cell(40, 4, "Etapa",     1, 0, 'C', 1);
  $oPdf->Cell(30, 4, "Vagas",     1, 0, 'C', 1);
  $oPdf->Cell(30, 4, "Ocupadas",  1, 1, 'C', 1);
}

function maior($iValor1, $iValor2) {

  $iAux = $iValor1;
  if ($iValor1 < $iValor2) {
    $iAux = $iValor2;
  }

  return $iAux;
}

$oPdf->Output();