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

require_once(modification("fpdf151/pdf.php"));

$oGet = db_utils::postMemory( $_GET );
define( "MSG_NAODESIGNADOS002", "educacao.matriculaonline.mol2_naodesignados002." );

/**
 * Monta o cabeçalho contendo a Etapa e as colunas necessárias para o relatório
 * @param  Pdf    $oPdf                
 * @param  array $aEtapasNaoDesignados
 */
function montaCabecalho( Pdf $oPdf, $aEtapasNaoDesignados ) {

  $oEtapaNaoDesignada = reset($aEtapasNaoDesignados);
  
  $oPdf->addpage();
  $oPdf->SetFont('Arial', 'B', 8);
  $oPdf->cell(192, 4, "ETAPA: " . $oEtapaNaoDesignada->nome_etapa, 0, 1 );
  $oPdf->cell(122, 4, 'Nome',                 1, 0, 'L', 1);
  $oPdf->cell(25,  4, 'Data Nascimento',      1, 0, 'C', 1);
  $oPdf->cell(45,  4, 'Telefones', 1, 1, 'C', 1);
  $oPdf->SetFont('Arial', '', 7);
}

try{

  if ( empty($oGet->iFase) ) {
    throw new Exception(  _M( MSG_NAODESIGNADOS002 . "informe_fase" ) );
  }

  $iEtapa  = isset( $oGet->iEtapa )  ? $oGet->iEtapa  : null;
  $iEscola = isset( $oGet->iEscola ) ? $oGet->iEscola : null;

  $oFaseModel = new Fase($oGet->iFase);

  if ( is_null($oFaseModel->getCodigo()) ) {
    throw new Exception( _M( MSG_NAODESIGNADOS002 . "fase_nao_encontrada" ) );  
  }

  $aNaoDesignados = $oFaseModel->listaNaoDesignados( $iEtapa, $iEscola );

  if ( empty($aNaoDesignados) ) {
    throw new Exception( _M( MSG_NAODESIGNADOS002 . "sem_registros" ) );
  }

  $head1 = "Relatório de Candidatos Não Designados";
  $head3 = "Fase:      " . db_stdClass::normalizeStringJsonEscapeString(trim($sFase));

  $aParametrosCabecalho = array();  

  if ( !empty($iEtapa) ) {

    $sFormataEtapa = db_stdClass::normalizeStringJsonEscapeString(trim($sEtapa));
    $aParametrosCabecalho[] = "Etapa:    " . substr($sFormataEtapa, 0, 25);
  }

  if ( !empty($iEscola) ) {

    $sFormataEscola = db_stdClass::normalizeStringJsonEscapeString(trim($sEscola));
    $aParametrosCabecalho[] = "Escola:   " . substr($sFormataEscola, 0, 25);
  }

  $head4 = implode("\n", $aParametrosCabecalho );

  $oPdf = new Pdf("P");
  $oPdf->Open();
  $oPdf->AliasNbPages();
  $oPdf->SetFillColor(240);

  foreach ($aNaoDesignados as $aEtapasNaoDesignados) {

    montaCabecalho( $oPdf, $aEtapasNaoDesignados);

    foreach ($aEtapasNaoDesignados as $oNaoDesignados ) {

      if ( $oPdf->getY() > 262) {
        montaCabecalho( $oPdf, $aEtapasNaoDesignados);
      }

      $aTelefones = array();

      if ( !empty($oNaoDesignados->celular_aluno) ) {
        $aTelefones[] = DBString::formatarTelefone($oNaoDesignados->celular_aluno);
      }
      
      if ( !empty($oNaoDesignados->telefone_responsavel) ) {
        $aTelefones[] = DBString::formatarTelefone($oNaoDesignados->telefone_responsavel);
      }

      $sTelefones = implode(" / ", $aTelefones);

      $oPdf->cell(122, 4, substr($oNaoDesignados->mo01_nome, 0, 73 ), 1, 0, 'L');
      $oPdf->cell(25, 4, $oNaoDesignados->data_nascimento,            1, 0, 'C');
      $oPdf->cell(45, 4, $sTelefones,                                 1, 1, 'C');
    }
  }

  $oPdf->Output();

} catch (Exception $oErro) {
  db_redireciona('db_erros.php?fechar=true&db_erro='.$oErro->getMessage());
}
