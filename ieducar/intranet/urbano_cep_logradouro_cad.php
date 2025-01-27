<?php
/**
 * i-Educar - Sistema de gestÃ£o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de ItajaÃ­
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa Ã© software livre; vocÃª pode redistribuÃ­-lo e/ou modificÃ¡-lo
 * sob os termos da LicenÃ§a PÃºblica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versÃ£o 2 da LicenÃ§a, como (a seu critÃ©rio)
 * qualquer versÃ£o posterior.
 *
 * Este programa Ã© distribuÃ­Â­do na expectativa de que seja Ãºtil, porÃ©m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implÃ­Â­cita de COMERCIABILIDADE OU
 * ADEQUAÃÃO A UMA FINALIDADE ESPECÃFICA. Consulte a LicenÃ§a PÃºblica Geral
 * do GNU para mais detalhes.
 *
 * VocÃª deve ter recebido uma cÃ³pia da LicenÃ§a PÃºblica Geral do GNU junto
 * com este programa; se nÃ£o, escreva para a Free Software Foundation, Inc., no
 * endereÃ§o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author      Prefeitura Municipal de ItajaÃ­ <ctima@itajai.sc.gov.br>
 * @license     http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package     Core
 * @subpackage  urbano
 * @subpackage  Enderecamento
 * @subpackage  Logradouro
 * @since       Arquivo disponÃ­vel desde a versÃ£o 1.0.0
 * @version     $Id$
 */
require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/urbano/geral.inc.php';

/*
require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';

require_once 'lib/Portabilis/Utils/Database.php';
require_once 'lib/Portabilis/String/Utils.php';

require_once 'Educacenso/Model/DocenteDataMapper.php';
*/
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' Cep Logradouro');
    $this->processoAp = '758';
    $this->addEstilo('localizacaoSistema');
  }
}
class indice extends clsCadastro
{
  var $pessoa_logada;
  var $idlog;
  var $nroini;
  var $nrofin;
  var $idpes_rev;
  var $data_rev;
  var $origem_gravacao;
  var $idpes_cad;
  var $data_cad;
  var $operacao;
  var $idpais;
  var $sigla_uf;
  var $idmun;
  var $tab_cep = array();
  var $cep;
  var $idbai;
  var $retorno;

  function Inicializar()
  {
    $this->retorno = 'Novo';
    
    $this->idlog = $_GET['idlog'];
    if (is_numeric($this->idlog)) {
      $obj_cep_logradouro = new clsUrbanoCepLogradouro();
      $lst_cep_logradouro = $obj_cep_logradouro->lista(NULL, NULL, NULL, NULL,
        NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $this->idlog);
      if ($lst_cep_logradouro) {
        $registro = $lst_cep_logradouro[0];
      }
      if ($registro) {
        foreach ($registro as $campo => $val) {
          $this->$campo = $val;
        }
        $this->retorno = 'Editar';
        // CEP
        $this->tab_cep = $this->getListCepBairro();
      }
    }
    else {
      $this->tab_cep[] = array();
    }
    $this->url_cancelar = $this->retorno == 'Editar' ?
      'urbano_cep_logradouro_det.php?idlog=' . $registro['idlog'] :
      'urbano_cep_logradouro_lst.php';
    $this->nome_url_cancelar = 'Cancelar';
    $nomeMenu = $this->retorno == "Editar" ? $this->retorno : "Cadastrar";
    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_enderecamento_index.php"    => "Endereçamento",
         ""        => "{$nomeMenu} CEP"
    ));
    $this->enviaLocalizacao($localizacao->montar());
    return $this->retorno;
  }
  function Gerar()
  {

    $habilitaCampo = ($this->retorno == 'Editar');

    // foreign keys
    $opcoes = array('' => 'Selecione');
    if (class_exists('clsPais')) {
      $objTemp = new clsPais();
      $lista = $objTemp->lista(FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');
      if (is_array($lista) && count($lista)) {
        foreach ($lista as $registro) {
          $opcoes[$registro['idpais']] = $registro['nome'];
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsPais nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }
    $this->campoLista('idpais', 'Pais', $opcoes, $this->idpais, '', FALSE, '','', $habilitaCampo);
    $opcoes = array('' => 'Selecione');
    if (class_exists('clsUf')) {
      if ($this->idpais) {
        $objTemp = new clsUf();
        $lista = $objTemp->lista(FALSE, FALSE, $this->idpais, FALSE, FALSE, 'nome ASC');
        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['sigla_uf']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsUf nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }
    $this->campoLista('sigla_uf', 'Estado', $opcoes, $this->sigla_uf, '', FALSE, '','', $habilitaCampo);
    $opcoes = array('' => 'Selecione');
    if (class_exists('clsMunicipio')) {
      if ($this->sigla_uf) {
        $objTemp = new clsMunicipio();
        $lista = $objTemp->lista(FALSE, $this->sigla_uf, FALSE, FALSE, FALSE,
          FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');
        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['idmun']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsMunicipio nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }
    $this->campoLista('idmun', 'Munic&iacute;pio', $opcoes, $this->idmun, '', FALSE, '','', $habilitaCampo);
    $opcoes = array('' => 'Selecione');
    if (class_exists('clsLogradouro')) {
      if ($this->idmun) {
        $objTemp = new clsLogradouro();
        $lista = $objTemp->lista(FALSE, FALSE, $this->idmun, FALSE, FALSE,
          FALSE, FALSE, 'nome ASC');
        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['idlog']] = $registro['nome'];
          }
        }
      }
    }
    else
    {
      echo '<!--\nErro\nClasse clsLogradouro nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }
    $this->campoLista('idlog', 'Logradouro', $opcoes, $this->idlog, '', FALSE, '','', $habilitaCampo);

    // Tabela CEP
    $this->campoTabelaInicio('tab_cep', 'Tabela de CEP', array('CEP', 'Bairro'), $this->tab_cep, 400);
    $opcoes_bairro = array('' => 'Selecione');
    if ($this->idmun) {
      $obj_bairro = new clsBairro();
      $lst_bairro = $obj_bairro->lista($this->idmun, FALSE, FALSE, FALSE, FALSE,
        'nome ASC');
      if ($lst_bairro) {
        foreach ($lst_bairro as $campo) {
          $opcoes_bairro[$campo['idbai']] = $campo['nome'];
        }
      }
    }
    $this->campoCep('cep', 'CEP', $this->cep, true);
    $this->campoLista('idbai', 'Bairro', $opcoes_bairro, $this->idbai);
    $this->campoTabelaFim();

    $scripts = array(
      '/modules/Portabilis/Assets/Javascripts/Utils.js',
      '/modules/Portabilis/Assets/Javascripts/ClientApi.js'
    );

    Portabilis_View_Helper_Application::loadJavascript($this, $scripts);
  }
  function Novo()
  {
    $this->Editar();
  }
  function Editar()
  {
    $this->idlog = !$this->idlog ? $_GET['idlog'] : $this->idlog;

    $tab_cep_aux = $this->getListCepBairro();

    if (($this->idbai[0] != '') && ($this->cep[0] != '')) {
      foreach ($this->cep as $id => $cep) {
        $cep = idFederal2int($cep);
        $obj = new clsUrbanoCepLogradouro($cep, $this->idlog, NULL, NULL, NULL,
          NULL, 'U', $this->pessoa_logada, NULL, 'I', NULL, 9);
        if (!$obj->existe()) {
          if (!$obj->cadastra()) {
            $this->mensagem = 'Cadastro n&atilde;o realizado.<br>';
            echo "<!--\nErro ao editar clsUrbanoCepLogradouro\nvalores obrigatorios\nif( is_numeric( $cep ) && is_numeric( $this->idlog ) && is_numeric( $this->pessoa_logada ) )\n-->";
            return FALSE;
          }
        }
        $obj_cep_log_bairro = new clsUrbanoCepLogradouroBairro($this->idlog,
          $cep, $this->idbai[$id], NULL, NULL, 'U', $this->pessoa_logada, NULL,
          'I', NULL, 9);

        if (!$obj_cep_log_bairro->existe()) {
          if ($id >= count($tab_cep_aux)){
            if (!$obj_cep_log_bairro->cadastra()) {
              $this->mensagem = 'Cadastro n&atilde;o realizado.<br>';
              echo "<!--\nErro ao editar clsUrbanoCepLogradouroBairro\nvalores obrigatorios\nif( is_numeric( $cep ) && is_numeric( $this->idlog ) && is_numeric( {$this->idbai[$id]} ) && is_numeric( $this->pessoa_logada ) )\n-->";
              return FALSE;
            }
          } else {
            $cepOld = idFederal2int($tab_cep_aux[$id][0]);
            $bairroOld = $tab_cep_aux[$id][1];
            if (!$obj_cep_log_bairro->editaCepBairro($cepOld, $bairroOld)) {
              $this->mensagem = 'Cadastro n&atilde;o realizado.<br>';
              echo "<!--\nErro ao editar clsUrbanoCepLogradouroBairro\nvalores obrigatorios\nif( is_numeric( $cep ) && is_numeric( $this->idlog ) && is_numeric( {$this->idbai[$id]} ) && is_numeric( $this->pessoa_logada ) )\n-->";
              return FALSE;
            }
          }
        }
      }
      $this->mensagem .= 'Edi&ccedil;&atilde;o efetuada com sucesso.<br>';
      $this->simpleRedirect('urbano_cep_logradouro_lst.php');
    }
    else {
      $this->simpleRedirect('urbano_cep_logradouro_lst.php');
    }
  }
  function Excluir()
  {
    $obj = new clsUrbanoCepLogradouro($this->cep, $this->idlog, $this->nroini,
      $this->nrofin, $this->idpes_rev, $this->data_rev, $this->origem_gravacao,
      $this->idpes_cad, $this->data_cad, $this->operacao);
    $excluiu = $obj->excluir();
    if ($excluiu) {
      $this->mensagem .= 'Exclus&atilde;o efetuada com sucesso.<br>';
      $this->simpleRedirect('urbano_cep_logradouro_lst.php');
    }
    $this->mensagem = 'Exclus&atilde;o n&atilde;o realizada.<br>';
    echo "<!--\nErro ao excluir clsUrbanoCepLogradouro\nvalores obrigatorios\nif( is_numeric( $this->cep ) && is_numeric( $this->idlog ) )\n-->";
    return FALSE;
  }
  function getListCepBairro()
  {
    $tab_cep = array();

    $obj_cep_logradouro_bairro = new clsCepLogradouroBairro();
    $lst_cep_logradouro_bairro = $obj_cep_logradouro_bairro->lista($this->idlog,
      FALSE, FALSE, 'cep ASC');
    if ($lst_cep_logradouro_bairro) {
      foreach ($lst_cep_logradouro_bairro as $cep) {
        $tab_cep[] = array(int2CEP($cep['cep']->cep), $cep['idbai']->idbai);
      }
    }
    return $tab_cep;
  }
}
// Instancia objeto de pÃ¡gina
$pagina = new clsIndexBase();
// Instancia objeto de conteÃºdo
$miolo = new indice();
// Atribui o conteÃºdo Ã   pÃ¡gina
$pagina->addForm($miolo);
// Gera o cÃ³digo HTML
$pagina->MakeAll();
?>

<script type="text/javascript" charset="toLatin1">
document.getElementById('idpais').onchange = function() {
  var campoPais = document.getElementById('idpais').value;
  var campoUf= document.getElementById('sigla_uf');
  campoUf.length = 1;
  campoUf.disabled = true;
  campoUf.options[0].text = 'Carregando estado...';
  var xml_uf = new ajax( getUf );
  xml_uf.envia('public_uf_xml.php?pais=' + campoPais);
}
function getUf(xml_uf) {
  var campoUf = document.getElementById('sigla_uf');
  var DOM_array = xml_uf.getElementsByTagName('estado');
  if (DOM_array.length) {
    campoUf.length = 1;
    campoUf.options[0].text = 'Selecione um estado';
    campoUf.disabled = false;
    for (var i = 0; i < DOM_array.length; i++) {
      campoUf.options[campoUf.options.length] = new Option( DOM_array[i].firstChild.data,
        DOM_array[i].getAttribute('sigla_uf'), false, false);
    }
  }
  else {
    campoUf.options[0].text = 'O pa\u00eds n\u00e3o possui nenhum estado';
  }
}
document.getElementById('sigla_uf').onchange = function() {
  var campoUf = document.getElementById('sigla_uf').value;
  var campoMunicipio= document.getElementById('idmun');
  campoMunicipio.length = 1;
  campoMunicipio.disabled = true;
  campoMunicipio.options[0].text = 'Carregando munic\u00edpio...';
  var xml_municipio = new ajax(getMunicipio);
  xml_municipio.envia('public_municipio_xml.php?uf=' + campoUf);
}
function getMunicipio(xml_municipio) {
  var campoMunicipio = document.getElementById('idmun');
  var DOM_array = xml_municipio.getElementsByTagName('municipio');
  if (DOM_array.length) {
    campoMunicipio.length = 1;
    campoMunicipio.options[0].text = 'Selecione um munic\u00edpio';
    campoMunicipio.disabled = false;
    for (var i = 0; i < DOM_array.length; i++) {
      campoMunicipio.options[campoMunicipio.options.length] = new Option(DOM_array[i].firstChild.data,
        DOM_array[i].getAttribute('idmun'), false, false);
    }
  }
  else {
    campoMunicipio.options[0].text = 'O estado n\u00e3o possui nenhum munic\u00edpio';
  }
}
document.getElementById('idmun').onchange = function() {
  var campoMunicipio = document.getElementById('idmun').value;
  var campoLogradouro = document.getElementById('idlog');
  campoLogradouro.length = 1;
  campoLogradouro.disabled = true;
  campoLogradouro.options[0].text = 'Carregando logradouro...';
  var xml_logradouro = new ajax(getLogradouro);
  xml_logradouro.envia('public_logradouro_xml.php?mun=' + campoMunicipio);
  for (var i = 0; i < tab_add_1.id; i++) {
    var campoBairro = document.getElementById('idbai['+i+']');
    campoBairro.length = 1;
    campoBairro.disabled = true;
    campoBairro.options[0].text = 'Carregando bairro...';
  }
  var xml_bairro = new ajax(getBairro);
  xml_bairro.envia('public_bairro_xml.php?mun=' + campoMunicipio);
}
function getLogradouro(xml_logradouro) {
  var campoLogradouro = document.getElementById('idlog');
  var DOM_array = xml_logradouro.getElementsByTagName('logradouro');
  if (DOM_array.length) {
    campoLogradouro.length = 1;
    campoLogradouro.options[0].text = 'Selecione um logradouro';
    campoLogradouro.disabled = false;
    for (var i = 0; i < DOM_array.length; i++) {
      if(DOM_array[i].firstChild){
      campoLogradouro.options[campoLogradouro.options.length] = new Option(DOM_array[i].firstChild.data,
        DOM_array[i].getAttribute('idlog'), false, false);
    }
    }
  }
  else {
    campoLogradouro.options[0].text = 'O munic\u00edpio n\u00e3o possui nenhum logradouro';
  }
}
function getBairro(xml_bairro) {
  var DOM_array = xml_bairro.getElementsByTagName('bairro');
  for (var i = 0; i < tab_add_1.id; i++) {
    var campoBairro = document.getElementById('idbai['+i+']');
    if (DOM_array.length) {
      campoBairro.length = 1;
      campoBairro.options[0].text = 'Selecione um bairro';
      campoBairro.disabled = false;
      for (var j = 0; j < DOM_array.length; j++) {
        campoBairro.options[campoBairro.options.length] = new Option(DOM_array[j].firstChild.data,
          DOM_array[j].getAttribute('idbai'), false, false);
      }
    }
    else {
      campoBairro.options[0].text = 'O munic\u00edpio n\u00e3o possui nenhum bairro';
    }
  }
}
document.getElementById('btn_add_tab_add_1').onclick = function() {
  tab_add_1.addRow();
  var campoMunicipio = document.getElementById('idmun').value;
  var pos = tab_add_1.id - 1;
  var campoBairro = document.getElementById('idbai['+pos+']');
  campoBairro.length = 1;
  campoBairro.disabled = true;
  campoBairro.options[0].text = 'Carregando bairro...';
  var xml_bairro = new ajax(getBairroUnico);
  xml_bairro.envia('public_bairro_xml.php?mun=' + campoMunicipio);
}
function getBairroUnico(xml_bairro) {
  var pos = tab_add_1.id - 1;
  var campoBairro = document.getElementById('idbai['+pos+']');
  var DOM_array = xml_bairro.getElementsByTagName('bairro');
  if (DOM_array.length) {
    campoBairro.length = 1;
    campoBairro.options[0].text = 'Selecione um bairro';
    campoBairro.disabled = false;
    for (var j = 0; j < DOM_array.length; j++) {
      campoBairro.options[campoBairro.options.length] = new Option(DOM_array[j].firstChild.data,
        DOM_array[j].getAttribute('idbai'), false, false);
    }
  }
  else {
    campoBairro.options[0].text = 'O munic\u00edpio n\u00e3o possui nenhum bairro';
  }
}

$j(document).ready(function(){

  for (var i = 0; i < tab_add_1.id; i++) {

    var valorCep = $j("input[id='cep["+i+"]']").val();
    var idBairro = $j("select[id='idbai["+i+"]'] option:selected").val();

    if (idBairro == '') continue;

    //Remove evento de click antigo
    $j("a[id='link_remove["+i+"]']").attr('onclick','').unbind('click');
    //Adiciona novo evento de click para excluir via Ajax

    $j("a[id='link_remove["+i+"]']").click({cep: valorCep, bairro: idBairro, button: document.getElementById('link_remove['+i+']')}, onclickExcluirCepBairro);
  }

  function onclickExcluirCepBairro(event) {
    if(!confirm("Tem certeza que deseja excluir este CEP?")) return false;

    var idLog    = $j("select[id='idlog'] option:selected").val();

    var options = {
      url       : deleteResourceUrlBuilder.buildUrl('/module/Api/endereco', 'delete_endereco'),
      dataType  : 'json',
      data      : {
        cep       : event.data.cep,
        id_bairro : event.data.bairro,
        id_log    : idLog
      },
      success   : function(dataResponse){
        if (!dataResponse.any_error_msg)
          tab_add_1.removeRow(event.data.button);

        handleDeleteCepBairro(dataResponse);
      }
    };
      deleteResource(options)
  }

  var handleDeleteCepBairro = function(dataResponse) {
    handleMessages(dataResponse.msgs);
  }

});

</script>
