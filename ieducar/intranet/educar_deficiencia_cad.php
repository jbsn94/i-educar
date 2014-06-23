<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	*																	     *
	*	@author Prefeitura Municipal de Itaja�								 *
	*	@updated 29/03/2007													 *
	*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
	*																		 *
	*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
	*						ctima@itajai.sc.gov.br					    	 *
	*																		 *
	*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
	*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
	*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
	*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
	*																		 *
	*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
	*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
	*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
	*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
	*																		 *
	*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
	*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
	*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
	*	02111-1307, USA.													 *
	*																		 *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
require_once ("include/clsBase.inc.php");
require_once ("include/clsCadastro.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/Geral.inc.php" );
require_once( "include/pmieducar/geral.inc.php" );
require_once "lib/Portabilis/String/Utils.php";

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Defici&ecirc;ncia" );
		$this->processoAp = "631";
		$this->addEstilo("localizacaoSistema");
	}
}

class indice extends clsCadastro
{
	/**
	 * Referencia pega da session para o idpes do usuario atual
	 *
	 * @var int
	 */
	var $pessoa_logada;

	var $cod_deficiencia;
	var $nm_deficiencia;
	var $deficiencia_educacenso;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->cod_deficiencia=$_GET["cod_deficiencia"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 631, $this->pessoa_logada, 7,  "educar_deficiencia_lst.php" );

		if( is_numeric( $this->cod_deficiencia ) )
		{

			$obj = new clsCadastroDeficiencia( $this->cod_deficiencia );
			$registro  = $obj->detalhe();
			if( $registro )
			{
				foreach( $registro AS $campo => $val )	// passa todos os valores obtidos no registro para atributos do objeto
					$this->$campo = $val;

				if( $obj_permissoes->permissao_excluir( 631, $this->pessoa_logada, 7 ) )
				{
					$this->fexcluir = true;
				}
				$retorno = "Editar";
			}
		}

		$this->url_cancelar = ($retorno == "Editar") ? "educar_deficiencia_det.php?cod_deficiencia={$registro["cod_deficiencia"]}" : "educar_deficiencia_lst.php";
		
		$nomeMenu = $retorno == "Editar" ? $retorno : "Cadastrar";
        $localizacao = new LocalizacaoSistema();
        $localizacao->entradaCaminhos( array(
             $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
             "educar_index.php"                  => "i-Educar - Escola",
             ""        => "{$nomeMenu} defici&ecirc;ncia"             
        ));
        $this->enviaLocalizacao($localizacao->montar());		

		$this->nome_url_cancelar = "Cancelar";
		return $retorno;
	}

	function Gerar()
	{
		// primary keys
		$this->campoOculto( "cod_deficiencia", $this->cod_deficiencia );

		// foreign keys

		// text
		$this->campoTexto( "nm_deficiencia", "Defici�ncia", $this->nm_deficiencia, 30, 255, true );

		$resources = array (  null => 'Selecione',
								 1 => "Cegueira",
								 2 => "Baixa vis�o",
								 3 => "Surdez",
								 4 => "Defici�ncia auditiva",
								 5 => "Surdocegueira",
								 6 => "Defici�ncia f�sica",
								 7 => "Defici�ncia Intelectual",
								 8 => "Defici�ncia M�ltipla",
								 9 => "Autismo Infantil",
								10 => "S�ndrome de Asperger",
								11 => "S�ndrome de Rett",
								12 => "Transtorno desintegrativo da inf�ncia",
								13 => "Altas habilidades/Superdota��o",);

		$options = array('label' => Portabilis_String_Utils::toLatin1('Defici�ncia Educacenso'), 'resources' => $resources, 'value' => $this->deficiencia_educacenso);
	    $this->inputsHelper()->select('deficiencia_educacenso', $options);

	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();



		$obj = new clsCadastroDeficiencia( $this->cod_deficiencia, $this->nm_deficiencia, $this->deficiencia_educacenso );
		$cadastrou = $obj->cadastra();
		if( $cadastrou )
		{
			$this->mensagem .= "Cadastro efetuado com sucesso.<br>";
			header( "Location: educar_deficiencia_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Cadastro n&atilde;o realizado.<br>";
		echo "<!--\nErro ao cadastrar clsCadastroDeficiencia\nvalores obrigatorios\nis_string( $this->nm_deficiencia )\n-->";
		return false;
	}

	function Editar()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();



		$obj = new clsCadastroDeficiencia($this->cod_deficiencia, $this->nm_deficiencia, $this->deficiencia_educacenso);
		$editou = $obj->edita();
		if( $editou )
		{
			$this->mensagem .= "Edi&ccedil;&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_deficiencia_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Edi&ccedil;&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao editar clsCadastroDeficiencia\nvalores obrigatorios\nif( is_numeric( $this->cod_deficiencia ) )\n-->";
		return false;
	}

	function Excluir()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();



		$obj = new clsCadastroDeficiencia($this->cod_deficiencia, $this->nm_deficiencia);
		$excluiu = $obj->excluir();
		if( $excluiu )
		{
			$this->mensagem .= "Exclus&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_deficiencia_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Exclus&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao excluir clsCadastroDeficiencia\nvalores obrigatorios\nif( is_numeric( $this->cod_deficiencia ) )\n-->";
		return false;
	}
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>
<script type="text/javascript">
	// Reescrita da fun��o para exibir mensagem interativa 
	function excluir()
    {
      document.formcadastro.reset();

      if (confirm('Deseja mesmo excluir essa defici�ncia? \nVinculos com os alunos ser�o deletados.')) {
        document.formcadastro.tipoacao.value = 'Excluir';
        document.formcadastro.submit();
      }
    }

</script>