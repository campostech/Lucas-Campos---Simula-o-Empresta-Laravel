<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QueryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //atribui os valores do request às variáveis
        $emprestimo = request("emprestimo");
        $instituicoes = request("instituicoes");
        $convenios = request("convenios");
        $parcela = request("parcela");

        //como o campo emprestimo é obrigatório, é verificado se o mesmo está no request
        if(!$emprestimo)
            return response()->json(['errors' => 'É necessário informar o valor do empréstimo'], 403);

        //abre o arquivo json que contem as taxas
        $filePath = storage_path('jsons/taxas_instituicoes.json');
        if (! file_exists($filePath)) {
            return response()->json(['errors' => 'Erro ao encontrar os dados...'], 403);
        }

        //define o valor do arquivo na variável 'fileJson' e posteriormente o decodifica na variável 'json'
        $fileJson = file_get_contents($filePath);
        $json = json_decode($fileJson, true); 

        //cria um array com o resultado dos objetos que serão utilizados de acordo com os parâmetros recebidos
        $result = array();

        //para cada objeto do json
        foreach ($json as &$value) {
            //verifica se foi especificada uma instituição e se sim verifica se cada instituição solicitada existe no objeto do json
            if($this->verifyJson($instituicoes, $value['instituicao'])){
                //verifica se foi especificado um convênio e se sim verifica se cada convênio solicitado existe no objeto do json
                if($this->verifyJson($convenios, $value['convenio'])){
                    //verifica se foi especificada o valor de parcelas e se sim verifica se o valor solicitado é o mesmo no objeto do json
                    if($parcela == null || $value['parcelas']==$parcela){
                        //como atendeu todos os requisitos do usuário o objeto é adicionado ao array 'result
                        array_push($result, $value);
                    }
                }
            }
        }
        //caso nenhum resultado foi obtido é retornado ao usuário uma resposta e se houve resultados, continua o fluxo
        if(count($result) == 0){
            return response()->json(['errors' => 'Não há dados com os parâmetros requisitados'], 403);
        }else{
            //cria um 'response' para armazenar os valores e para cada objeto do 'result' é criado um objeto para o 'reponse' 
            $response = [];
            foreach ($result as &$value) {
                $newValue = array(
                    'taxa' => $value['taxaJuros'],
                    'parcelas' => $value['parcelas'],
                    //o valor da parcela é o valor do coeficiente multiplicado pelo valor do empréstimo
                    //para melhor visualização, o resultado é arredondado para 2 casas decimais
                    'valor_parcela' => round($emprestimo*$value['coeficiente'],2),
                    'convenio' => $value['convenio'],
                );
                //se ainda não nenhum objeto para aquela instituição é criado um array vazio para receber outros objetos
                if(!isset($response[$value['instituicao']])) $response[$value['instituicao']]= array();
                //é inserido o objeto criado anteriormente a sua respectiva instituição
                array_push($response[$value['instituicao']], $newValue);
            }
            //é retornado o objeto final com todos os objetos de todas as instituições que atenderam os parâmetros do usuário
            return response()->json($response, 200);
        }
    }

    public function verifyJson($arr,$vArg){
        //a principio o retorno será negativo, pois caso a variável '$return' não seja alterada e seja solicitado pelo usuário um parâmetro específico, o retorno da função será negativo
        $return = false;
        //caso não o usuário não tenha especificido o parâmetro solicitado, ele retorna positivo
        if($arr == null)
            return true;
        //para cada objeto do array é feita a verificação se aquele valor é igual ao solicitado pelo usuário, definindo então a resposta como positiva
        foreach($arr as &$value){
            if($vArg==$value)
                $return = true;
        }
        //retorna a variável que pode ou não ter sido alterada
        return $return;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
