<?php
class UsuarioController extends Controller
{
    /**
     * @param string $id
     * @param string $name
     */
    public function Cadastrar($id='', $name=''){
        $this->view(['id' =>$id, 'name' =>$name]);
        $this->view->page_title = 'Cadastrar';
        $this->view->render();
    }

    /**
     * @param string $id
     * @param string $name
     */
    public function Login($id='', $name=''){
        //Manda as variaveis para a view
        $this->view(['id' =>$id, 'name' =>$name]);

        //Muda o titulo da view <title>Titulo</title>
        $this->view->page_title = 'Login';

        //Mostra a view para o usuario site/Usuario/Login
        $this->view->render();
    }

    /**
     * Ele retorna um json com o usuario requisitado ou todos, sendo necessario Autenticação
     * @param string $id
     */
    public function Listar(){
        $token  = Token::getTokenFromHeadersOrSession('Token','Authorization');
        $Usuarios = Assert::equalsOrError(Usuarios::findById($token->id)->admin,true) ? Usuarios::findAll() : ['erro'=>'Autenticação é requerida'];
        header("Content-type:application/json");
        echo json_encode($Usuarios);
    }

    /**
     * Metodo Cadastra o Usuario no banco de dados via Formulario "POST"
     */
    public function cadastrar_post(){
        $json = json_decode(file_get_contents('php://input'), true);

        $Usuario = new Usuarios();
        $Usuario->nome  = ($json) ? filter_var($json['nome'],  FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'nome');
        $Usuario->senha = ($json) ? filter_var($json['senha'], FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'senha');
        $Usuario->email = ($json) ? filter_var($json['email'], FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'email');
        $Usuario->foto_perfil = ($json) ? filter_var($json['foto_perfil'], FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'foto_perfil');
        $Usuario->senha = password_hash($Usuario->senha, PASSWORD_BCRYPT);
        $Usuario->admin = "0";
        $Usuario->save();
        header('Location:' . '/Usuario/Cadastrar');
    }

    /**
    * Metodo Autentica o usuario de acordo com o email e senha
    */
    public function login_post(){
        $email = filter_input(INPUT_POST, 'email');
        $senha = filter_input(INPUT_POST, 'senha');
        $Usuario = Usuarios::findBy('email',$email);

        if(isset($Usuario)){
            $erro = "";

            if($email != $Usuario['email']){
                $erro = "Por favor verifique as informações digitadas";
            }

            if(password_verify($senha,$Usuario['senha'])){
                $erro = "Por favor verifique as informações digitadas";
            }

            if($erro == ""){
                $payload = [
                    'id'   =>$Usuario['id'],
                    'email'=>$Usuario['email'],
                    'sala'=>null,
                    'time_sala'=> null,
                    'time_ativo'=> null
                ];

                $_SESSION['Token'] = Token::encode($payload);
                header('Location:' . '/Index/Index');
            }else{
                header('Location:' . '/Usuario/Login/1');
            }
        }
    }

}