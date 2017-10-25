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

define('MSG_MOL2INSCRITOS002', 'educacao.matriculaonline.mol2_inscritos002.');
$aDadosRelatorio = array();

$oEscola = null;
$oEtapa  = null;

try {

  if ( !empty($oGet->iEscola) ) {
    $oEscola = EscolaRepository::getEscolaByCodigo($oGet->iEscola);

    if ( is_null($oEscola->getCodigo()) ) {
      throw new Exception( _M( MSG_MOL2INSCRITOS002 . "escola_nao_encontrada" ) );
    }
  }
  if ( !empty($oGet->iEtapa) ) {
    $oEtapa = EtapaRepository::getEtapaByCodigo($oGet->iEtapa);

    if ( is_null($oEtapa->getCodigo()) ) {
      throw new Exception( _M( MSG_MOL2INSCRITOS002 . "etapa_nao_encontrada" ) );
    }
  }

  if ( empty($oGet->iFase) ) {
    throw new Exception( _M( MSG_MOL2INSCRITOS002 . "fase_nao_informada" ) );
  }

  $oFase = new Fase($oGet->iFase);

  if( is_null($oFase->getCodigo()) ) {
    throw new Exception( _M( MSG_MOL2INSCRITOS002 . "fase_nao_informada" ) );
  }


  if ( $oGet->sModelo == 'A' ) {
    $aDadosRelatorio = buscaDadosAnalitico($oFase, $oEscola, $oEtapa);
  } else {
    $aDadosRelatorio = buscaDadosSintetico($oFase, $oEscola, $oEtapa);
  }



} catch ( Exception $e ) {

  $sMsg = $e->getMessage();
  db_redireciona("db_erros.php?fechar=true&db_erro={$sMsg}");
}


function buscaDadosAnalitico($oFase, $oEscola, $oEtapa) {

  $sCampos  = " mo02_escola        as cod_escola, ";
  $sCampos .= " ed10_i_codigo      as cod_ensino, ";
  $sCampos .= " ed11_i_codigo      as cod_etapa, ";
  $sCampos .= " trim(ed18_c_nome)  as escola,   ";
  $sCampos .= " trim(ed11_c_descr) as etapa,    ";
  $sCampos .= " trim(ed10_c_descr) as ensino,   ";
  $sCampos .= " mo01_nome     as nome,          ";
  $sCampos .= " mo03_opcao    as opcao,         ";
  $sCampos .= " mo01_telresp  as telefone_resp, ";
  $sCampos .= " mo01_telcel   as telefone,      ";
  $sCampos .= " mo01_dtnasc   as nascimento,    ";
  $sCampos .= " ed11_i_sequencia, ";
  $sCampos .= " (select sum(mo10_numvagas) ";
  $sCampos .= "    from plugins.vagas ";
  $sCampos .= "   where mo12_fase   = mo10_fase ";
  $sCampos .= "     and mo02_escola = mo10_escola ";
  $sCampos .= "     and mo01_serie  = mo10_serie ";
  $sCampos .= " ) as total_vagas_serie ";

  $sWhere   = " mo12_fase = {$oFase->getCodigo()} ";

  if ( !empty($oEscola)) {
    $sWhere  .= " and mo02_escola = {$oEscola->getCodigo()} ";
  }
  if ( !empty($oEtapa) ) {
    $sWhere  .= " and mo01_serie = {$oEtapa->getCodigo()} ";
  }
  $sOrdem = " escola, ed10_ordem, ed11_i_sequencia, mo01_nome ";

  $oDaoInscritos = new cl_mobase();
  $sSqlInscritos = $oDaoInscritos->sql_query_inscritos(null, $sCampos, $sOrdem, $sWhere);
  $rsInscritos   = db_query($sSqlInscritos);

  $oMsgErro = new stdClass();
  if (!$rsInscritos) {

    $oMsgErro->sErro = pg_last_error();
    throw new DBException (_M(MSG_MOL2INSCRITOS002 . "erro_buscar_inscritos", $oMsgErro));
  }

  $iLinhas = pg_num_rows($rsInscritos);

  if( $iLinhas == 0 ) {
    throw new BusinessException (_M(MSG_MOL2INSCRITOS002 . "fase_sem_registros", $oMsgErro));
  }

  $aDadosRelatorio = array();
  for ($i = 0; $i < $iLinhas; $i++) {

    $oDados  = db_utils::fieldsMemory($rsInscritos, $i);
    $iEscola = $oDados->cod_escola;
    $iEtapa  = $oDados->cod_etapa;

    if ( !array_key_exists($iEscola, $aDadosRelatorio)) {

      $oEscola          = new stdClass();
      $oEscola->sEscola = $oDados->escola;
      $oEscola->aEtapas = array();

      $aDadosRelatorio[$iEscola] = $oEscola;
    }

    if ( !array_key_exists($iEtapa, $aDadosRelatorio[$iEscola]->aEtapas) ) {

      $oEtapa              = new stdClass();
      $oEtapa->sEtapa      = $oDados->etapa;
      $oEtapa->sEnsino     = $oDados->ensino;
      $oEtapa->iVagasEtapa = $oDados->total_vagas_serie;
      $oEtapa->aInscritos  = array();

      $aDadosRelatorio[$iEscola]->aEtapas[$iEtapa] = $oEtapa;
    }
    $aDadosRelatorio[$iEscola]->aEtapas[$iEtapa]->aInscritos[] = $oDados;
  }

  return $aDadosRelatorio;
}

function buscaDadosSintetico($oFase, $oEscola, $oEtapa) {

  $sCampos  = " ed18_i_codigo,                ";
  $sCampos .= " ed10_ordem,                   ";
  $sCampos .= " ed11_i_codigo,                ";
  $sCampos .= " mo04_codigo,                  ";
  $sCampos .= " trim(ed18_c_nome)  as escola, ";
  $sCampos .= " trim(ed11_c_descr) as etapa,  ";
  $sCampos .= " trim(ed10_c_descr) as ensino, ";
  $sCampos .= " sum(mo10_numvagas) as total_vagas_serie,  ";
  $sCampos .= " (select count(*) from mobase  ";
  $sCampos .= "   inner join basefase   on mo01_codigo = mo12_base ";
  $sCampos .= "   inner join baseescola on mo01_codigo = mo02_base ";
  $sCampos .= "   where mo02_escola = ed18_i_codigo ";
  $sCampos .= "     and mo12_fase   = mo04_codigo   ";
  $sCampos .= "     and mo01_serie  = ed11_i_codigo ";
  $sCampos .= " ) as total_inscritos ";

  $sWhere   = "     mo04_codigo = {$oFase->getCodigo()} ";
  $sWhere  .= " and mo10_numvagas > 0 ";

  if ( !empty($oEscola)) {
    $sWhere  .= " and ed18_i_codigo = {$oEscola->getCodigo()} ";
  }
  if ( !empty($oEtapa) ) {
    $sWhere  .= " and ed11_i_codigo = {$oEtapa->getCodigo()} ";
  }
  $sOrdem = " escola, ed10_ordem, ed11_i_sequencia ";
  $sGroup = " group by ed18_i_codigo, ed10_ordem, ed11_i_codigo, mo04_codigo, escola, etapa, ensino ";

  $oDaoVagas      = new cl_vagas();
  $sSqlInscritos = $oDaoVagas->sql_query_escola_serie_ensino(null, $sCampos, $sOrdem, $sWhere . $sGroup );
  $rsInscritos   = pg_query($sSqlInscritos);

  $oMsgErro = new stdClass();
  if (!$rsInscritos) {

    $oMsgErro->sErro = pg_last_error();
    throw new DBException (_M(MSG_MOL2INSCRITOS002 . "erro_buscar_inscritos", $oMsgErro));
  }

  $iLinhas = pg_num_rows($rsInscritos);

  if( $iLinhas == 0 ) {
    throw new BusinessException (_M(MSG_MOL2INSCRITOS002 . "fase_sem_registros", $oMsgErro));
  }

  $aDadosRelatorio = array();
  for ($i = 0; $i < $iLinhas; $i++) {

    $oDados  = db_utils::fieldsMemory($rsInscritos, $i);
    $iEscola = $oDados->ed18_i_codigo;
    $iEtapa  = $oDados->ed11_i_codigo;

    if ( !array_key_exists($iEscola, $aDadosRelatorio)) {

      $oEscola          = new stdClass();
      $oEscola->sEscola = $oDados->escola;
      $oEscola->aEtapas = array();

      $aDadosRelatorio[$iEscola] = $oEscola;
    }

    if ( !array_key_exists($iEtapa, $aDadosRelatorio[$iEscola]->aEtapas) ) {

      $oEtapa              = new stdClass();
      $oEtapa->sEtapa      = $oDados->etapa;
      $oEtapa->sEnsino     = $oDados->ensino;
      $oEtapa->iVagasEtapa = $oDados->total_vagas_serie;
      $oEtapa->iInscritos  = 0;

      $aDadosRelatorio[$iEscola]->aEtapas[$iEtapa] = $oEtapa;
    }
    $aDadosRelatorio[$iEscola]->aEtapas[$iEtapa]->iInscritos = $oDados->total_inscritos;
  }

  return $aDadosRelatorio;

}


$aParametrosCabecalho = array();

$head1 = "Relatório de Inscritos";
$head3 = "Fase: " . $oFase->getDescricao();

if ( !empty($oEtapa) ) {
  $aParametrosCabecalho[] = "Etapa: {$oEtapa->getNome()}";
}

if ( !empty($oEscola) ) {
  $aParametrosCabecalho[] = "Escola: {$oEscola->getNome()}";
}

$aParametrosCabecalho[] = "Modelo: " . ($oGet->sModelo == 'A' ? 'Analítico' : 'Sintético');

$head4 = implode("\n", $aParametrosCabecalho);

$oPdf = new PDF('P');
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetMargins(8, 10);
$oPdf->SetAutoPageBreak(false, 20);
$oPdf->SetFillColor(240);

if ( $oGet->sModelo == 'A' ) {
  imprimeModeloAnalitico($oPdf, $aDadosRelatorio);
} else {
  imprimeModeloSintetico($oPdf, $aDadosRelatorio);
}

$oPdf->Output();
function imprimeModeloAnalitico(PDF $oPdf, $aDadosRelatorio) {

  $lPrimeiraEsola = true;
  foreach ($aDadosRelatorio as $oDadosEscola) {

    if ($lPrimeiraEsola || ($oPdf->GetY() > $oPdf->h - 30) ) {

      $oPdf->AddPage();
      $lPrimeiraEsola = false;
    }
    $oPdf->SetFont('Arial', 'B', 8);
    $oPdf->Cell(193, 4, $oDadosEscola->sEscola, 0, 1);
    $oPdf->ln(2);

    $lPrimeiraEtapa = true;
    foreach ($oDadosEscola->aEtapas as $oDadosEtapa) {

      $lQuebraPagina = false;
      if ( $oPdf->GetY() > $oPdf->h - 30 ) {
        $lQuebraPagina = true;
      }

      imprimeCabecalhoAnalitico($oPdf, $oDadosEtapa, $oDadosEscola->sEscola, $lQuebraPagina);
      foreach ($oDadosEtapa->aInscritos as $oInscrito) {

        if ( $oPdf->GetY() > $oPdf->h - 20 ) {
          imprimeCabecalhoAnalitico($oPdf, $oDadosEtapa, $oDadosEscola->sEscola, true);
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
    $lPrimeiraEsola = true;
  }
}

function imprimeCabecalhoAnalitico($oPdf, $oDadosEtapa, $sEscola, $lAdicionaPagina) {

  $oPdf->SetFont('Arial', 'B', 8);
  if ($lAdicionaPagina) {

    $oPdf->AddPage();
    $oPdf->Cell(193, 4, $sEscola, 0, 1);
  }

  $oPdf->Cell(100, 4, $oDadosEtapa->sEtapa,      0, 0);
  $oPdf->Cell(50,  4, "Vagas: "  . $oDadosEtapa->iVagasEtapa, 0, 0);
  $oPdf->Cell(43,  4, "Inscritos: " . count($oDadosEtapa->aInscritos), 0, 1);

  $oPdf->Cell(90, 4, "Aluno",      1, 0, 'C', 1);
  $oPdf->Cell(20, 4, "Nascimento", 1, 0, 'C', 1);
  $oPdf->Cell(15, 4, "Opção",      1, 0, 'C', 1);
  $oPdf->Cell(70, 4, "Telefones",  1, 1, 'C', 1);

}


function imprimeModeloSintetico($oPdf, $aDadosRelatorio) {

  $lPrimeiraEsola = true;
  foreach ($aDadosRelatorio as $oDadosEscola) {

    if ($lPrimeiraEsola || ($oPdf->GetY() > $oPdf->h - 20) ) {

      $oPdf->AddPage();
      $lPrimeiraEsola = false;
    }
    $oPdf->SetFont('Arial', 'B', 8);
    imprimeCabecalhoSintetico($oPdf, $oDadosEscola->sEscola, false);

    $iTotalVagasDisponiveis = 0;
    $iTotalInscritos        = 0;
    foreach ($oDadosEscola->aEtapas as $oDadosEtapa) {

      $lQuebraPagina = false;
      if ( $oPdf->GetY() > $oPdf->h - 20 ) {

        $lQuebraPagina = true;
        imprimeCabecalhoSintetico($oPdf, $oDadosEscola->sEscola, $lQuebraPagina);
      }

      $iInscritos = $oDadosEtapa->iInscritos;
      $oPdf->SetFont('Arial', '', 7);
      $oPdf->Cell(95, 4, "{$oDadosEtapa->sEnsino}",     1, 0, 'L');
      $oPdf->Cell(40, 4, "{$oDadosEtapa->sEtapa}",      1, 0, 'C');
      $oPdf->Cell(30, 4, "{$oDadosEtapa->iVagasEtapa}", 1, 0, 'C');
      $oPdf->Cell(30, 4, "{$iInscritos}",               1, 1, 'C');

      $iTotalVagasDisponiveis += $oDadosEtapa->iVagasEtapa;
      $iTotalInscritos        += $iInscritos;
    }

    $oPdf->SetFont('Arial', 'B', 8);
    $oPdf->Cell(135, 4, "Total de Vagas",            1, 0, 'R', 1);
    $oPdf->Cell(30,  4, "{$iTotalVagasDisponiveis}", 1, 0, 'C', 1);
    $oPdf->Cell(30,  4, "{$iTotalInscritos}",        1, 1, 'C', 1);
    $oPdf->ln();

  }
}

function imprimeCabecalhoSintetico($oPdf, $sEscola, $lQuebraPagina) {

  $oPdf->SetFont('Arial', 'B', 8);
  if ($lQuebraPagina) {
    $oPdf->AddPage();
  }
  $oPdf->Cell(193, 4, $sEscola, 0, 1);
  $oPdf->Cell(95,  4, "Ensino",    1, 0, 'C', 1);
  $oPdf->Cell(40,  4, "Etapa",     1, 0, 'C', 1);
  $oPdf->Cell(30,  4, "Vagas ",    1, 0, 'C', 1);
  $oPdf->Cell(30,  4, "Inscritos", 1, 1, 'C', 1);
}

function maior($iValor1, $iValor2) {

  $iAux = $iValor1;
  if ($iValor1 < $iValor2) {
    $iAux = $iValor2;
  }

  return $iAux;
}