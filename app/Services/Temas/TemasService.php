<?php 
    namespace App\Services\Temas;
    use Illuminate\Support\Facades\Auth;
    use App\Models\User_tema;
    use App\Models\Temas;
    use App\Models\Questoes;
    use App\Models\ConjuntoQuestoes;

    class TemasService {
        public function listarTemas() {
            $userId = Auth::user()->id;
            $temas =  User_tema::where("user_id", "=", $userId)->get();

            $temasUsuario = [];
            foreach ($temas as $tema) {
                $nomeTema = Temas::where("id", "=", $tema->tema_id)->first();
                $nomeTema = $nomeTema->nome;
                array_push($temasUsuario, [
                    "tema" => $nomeTema,
                    "temaId" => $tema->tema_id,
                    "conclusao" => $tema->conclusao
                ]);
            }
            return $temasUsuario;
        }

        public function questoesTema($idTema) {
            $tema = Temas::where("id", "=", $idTema)->first();
            if(!isset($tema)) {
                return json_encode([
                    'status'   => 'error',
                    'mensagem' => "Id inválido"                    
                ]);
            }
            $questoes = [
                "conjunto1_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto1_id"])->first(),
                "conjunto2_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto2_id"])->first(),
                "conjunto3_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto3_id"])->first(),
                "conjunto4_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto4_id"])->first(),
                "conjunto5_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto5_id"])->first(),
                "conjunto6_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto6_id"])->first(),
                "conjunto7_id" => ConjuntoQuestoes::where("id", "=", $tema["conjunto7_id"])->first()
            ];

            for($i=1; $i <= 7; $i++) {
                unset($questoes["conjunto".$i."_id"]["id"]);
                unset($questoes["conjunto".$i."_id"]["created_at"]);
                unset($questoes["conjunto".$i."_id"]["updated_at"]);                
                for($x=1; $x <= 7; $x++) {
                    $questoes["conjunto".$i."_id"]["questao".$x."_id"] = Questoes::where("id", "=",$questoes["conjunto".$i."_id"]["questao".$x."_id"])->first();
                    unset($questoes["conjunto".$i."_id"]["questao".$x."_id"]["id"]);
                    unset($questoes["conjunto".$i."_id"]["questao".$x."_id"]["created_at"]);
                    unset($questoes["conjunto".$i."_id"]["questao".$x."_id"]["updated_at"]);
                }
            }
            return $questoes;
        }
    }

?>