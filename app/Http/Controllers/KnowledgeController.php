<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Knowledge;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class KnowledgeController extends Controller
{
    private $knowledge;
    function __construct(Knowledge $knowledge) {
       $this->knowledge = $knowledge;
    }

    public function criarMentorSobreAssunto($id)
    {
        return view('edits.editarMentor', compact('id'));
    }
    
    public function store(Request $request)
    {
        $this->validate($request, $this->knowledge->rules, $this->knowledge->messsages);
        $knowledge = new Knowledge([
           'knowledge_nivel' => $request->knowledge_nivel, 
           'knowledge_rank' => 5, 
           'fk_knowledge_user' => Auth::user()->user_id,
           'fk_knowledge_subject' => $request->fk_user_subject
        ]);
        $mentor = Knowledge::where('fk_knowledge_user', '=', Auth::user()->user_id)->
                             where('fk_knowledge_subject', '=', $request->fk_user_subject)->count();
        if($mentor == 0){
            try
            {
                $knowledge->save();
                return array('bool' => false);
            } catch (QueryException $ex) {
                return array('bool' => true, 'motivo' => '');
            }
        }
        else
        {
            return array('bool' => true, 'motivo' => 'Não pode haver mentoria no mesmo assunto');
        }
    }
    public function alteraMentor(Request $request)
    {
        $mentor = Knowledge::where('fk_knowledge_user', Auth::user()->user_id)
                           ->where('fk_knowledge_subject', $request->fk_knowledge_subject);
        if($mentor->count() == 0)
        {
            $this->validate($request, ['knowledge_nivel' => 'required'], ['knowledge_nivel.required' => 'Nivel obrigatorio']);
            $knowledge = new Knowledge([
               'knowledge_nivel' => $request->knowledge_nivel, 
               'knowledge_rank' => 5, 
               'fk_knowledge_user' => Auth::user()->user_id,
               'fk_knowledge_subject' => $request->fk_knowledge_subject
            ]);
            try
            {
                $knowledge->save();
                return redirect('Mentoria_no_assunto')->with('success', 'Alterado com sucesso');
            } catch (QueryException $ex) {
                return redirect('Mentoria_no_assunto')->with('failure', 'Deu alguma merda ein');
            }
        }
        else
        {
            $knowledge = $mentor->first();
            $knowledge->knowledge_nivel = $request->knowledge_nivel;
            try
            {
                $knowledge->update();
                return redirect('Mentoria_no_assunto')->with('success', 'Alterado com sucesso');
            } catch (QueryException $ex) {
                return redirect('Mentoria_no_assunto')->with('failure', 'Deu alguma merda ein');
            }
            
        }
    }
    public function show($id)
    {
        $mentorias = Knowledge::join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                ->where('fk_knowledge_user', $id)->get();
        $mentoria = array();
        foreach ($mentorias as $m) {
            $submentoria = array();
            $submentoria['assunto'] = $m->subject_name;
            $submentoria['assunto_id'] = $m->subject_id;
            $submentoria['nivel'] = intval($m->knowledge_nivel);
            $submentoria['rank'] = intval($m->knowledge_rank);
            $submentoria['ativo'] = boolval($m->knowledge_active);
            $submentoria['ativar'] = ($m->knowledge_active) ?       
                                        "<form method='POST' action='".route('ativarmentor', $m->knowledge_id)."'>".
                                            method_field('PATCH').
                                            @csrf_field().
                                        "<button type='submit' role='button' class='btn btn-warning' data-toggle='tooltip' title='Inativar Item'><i class='fa fa-times'></i></button> </span></button> </form>" : 
                                        "<form method='POST' action='".route('ativarmentor', $m->knowledge_id)."'>".
                                            method_field('PATCH').
                                            @csrf_field().
                                        "<button type='submit' role='button' class='btn btn-success' data-toggle='tooltip' title='Ativar Item'><i class='fa fa-check'></i></button> </button></form>";;
            $mentoria[] = $submentoria;
        }
        echo json_encode($mentoria);
    }

    public function showEditar(Request $request)
    {
        $id = $request->subject_id; 
        $user = Knowledge::join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                ->where('fk_knowledge_user', $id)->get();
        $mentoria = array();
        foreach ($mentorias as $m) {
            $submentoria = array();
            $submentoria['assunto'] = $m->subject_name;
            $submentoria['assunto_id'] = $m->subject_id;
            $submentoria['nivel'] = intval($m->knowledge_nivel);
            $submentoria['rank'] = intval($m->knowledge_rank);
            $submentoria['ativo'] = boolval($m->knowledge_active);
            $submentoria['ativar'] = ($m->knowledge_active) ?       
                                        "<form method='POST' action='".route('ativarmentor', $m->knowledge_id)."'>".
                                            method_field('PATCH').
                                            @csrf_field().
                                        "<button type='submit' role='button' class='btn btn-warning' data-toggle='tooltip' title='Inativar Item'><i class='fa fa-times'></i></button> </span></button> </form>" : 
                                        "<form method='POST' action='".route('ativarmentor', $m->knowledge_id)."'>".
                                            method_field('PATCH').
                                            @csrf_field().
                                        "<button type='submit' role='button' class='btn btn-success' data-toggle='tooltip' title='Ativar Item'><i class='fa fa-check'></i></button> </button></form>";;
            $mentoria[] = $submentoria;
        }
        echo json_encode($mentoria);
    }
    public function ativarMentor($id)
    { 
        $knowledge = Knowledge::find($id);

        if($knowledge->knowledge_active == true) $knowledge->knowledge_active = false;
        else if ($knowledge->knowledge_active == false) $knowledge->knowledge_active = true;
        else $knowledge->knowledge_active = false;
        try
        {
            $knowledge->update();
            return back()->with('success', 'Status alterado!');
          
         } catch (QueryException $ex) {
            return back()->with('failure', 'ERRO! Status não alterado!');
         }

    }
    
    public function edit($id)
    {
        $mentor = Knowledge::find($id);
        return redirect('EditarMentor')->with('mentor', $mentor);
    }

    public function atulizarRank($id)
    {
        $mentor = Knowledge::find($id);
        $novo = doubleval($mentor->knowledge_rank);
        $novo = ($novo + $request->rank) / 2;
        $mentor->knowledge_rank = $novo;
        try
        {
            $mentor->update();
            redirect('naoseideondevem')->with('success', 'Obrigado pela avaliação');
        }
        catch (QueryException $ex)
        {
            redirect('naoseideondevem')->with('failure', 'Erro ao dar a avaliação');
        }
        
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, $this->mentor->rules, $this->mentor->messsages);
        $mentor = Knowledge::find($id);
        $mentor->knowledge_nivel = $request->knowledge_nivel;
        try
        {
            $mentor->update();
            return redirect('mentoresAdmin')->with('success', 'Mentor alterado');
        } catch (QueryException $ex) {
            return redirect('mentoresAdmin')->with('failure', 'Mentor não alterado');
        }
    }

    public function destroy($id)
    {
        $mentor = Knowledge::find($id);
        $user = \App\UserSubject::where('fk_user_subject', '=', intval($mentor->fk_knowledge_subject))->
                           where('fk_subject_user', '=', intval($mentor->fk_knowledge_user));
        try
        {
            $user->delete();
            $mentor->delete();
            return redirect('mentoresAdmin')->with('success', 'Mentor deletado');
        } catch (QueryException $ex) {
            return redirect('mentoresAdmin')->with('failure', 'Mentor não deletado');
        }
    }


    public function PegaDadosKnowledge(Request $request) {

        $pegadados = $this->CriarDataTable($request);
        $dados = array();


        foreach ($pegadados as $row) {

            if($row->connection_status == null) {

            
                $sub_dados = array();
                $sub_dados[] = $row->subject_name; //subject_name
                $sub_dados[] = $row->knowledge_nivel;  //knowledge_nivel
                $sub_dados[] = $row->user_nome; //user_nome
                $sub_dados[] = $row->knowledge_rank; //rank
                $sub_dados[] = 
            
            
            "<form method='POST' action='".route('conexao', $row->knowledge_id)."'>".
                            method_field('POST'). 
                            @csrf_field().
            "<button type='submit' role='button' class='btn btn-success' data-toggle='tooltip' title='Solicitar'>Solicitar Mentoria</button> </span></button> </form>";
            
            
            $dados[] = $sub_dados;
            }
        }
        
        $output = array (
            "draw"  => intval($request->draw),
            "recordsTotal" => $this->TodosRegistros(), 
            "recordsFiltered" => $this->RegistrosFiltrados($request),
            "data" => $dados
        );
        echo json_encode($output);
    }



    private $order = ['subject_name','knowledge_nivel', 'user_nome','knowledge_rank', null];

    public function CriarQuery(Request $request)
    {
        $assunto = Knowledge::select('subject_id')
                            ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                            ->join('usersubjects', 'subject_id', '=', 'fk_user_subject')
                            ->where('fk_subject_user', Auth::user()->user_id)
                            ->where('fk_knowledge_user', '<>', Auth::user()->user_id)
                            ->where('knowledge_active', true)->get();  
        $assuntos = array();
        foreach ($assunto as $value) {
            $assuntos[] = $value->subject_id;
        }
        $this->knowledge = Knowledge::select('knowledge_id', 'subject_name','knowledge_nivel', 'user_nome', 'knowledge_rank', 'connection_status')
        ->join('users', 'user_id', '=', 'fk_knowledge_user')
            ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                ->leftJoin('connections', 'fk_connection_knowledge', '=', 'knowledge_id')
                        ->whereIn('subject_id', $assuntos);            
                //não pode aparecer mentores que ja estão conectados
                //opção cancelar na lista de mentores, caso o mentor já tenha sido solicitado para iniciar uma mentoria
        if($request->input('search.value') != null)
        {
            $this->knowledge->where('user_nome', 'like' ,'%', $request->input('search.value'));            
        }
        if($request->order!= null)
        {
            $this->knowledge->orderBy(array_get($this->order, $request->input('order.0.column')),
                                $request->input('order.0.dir'));
        }
        else
        {
            $this->knowledge->orderBy('knowledge_rank', 'desc');
        }
    }
    
    public function PegaDadosKnowledgeAdmin(Request $request) {

        $pegadados = $this->CriarDataTableAdmin($request);
        $dados = array();
        foreach ($pegadados as $row) {
            $sub_dados = array();
            $sub_dados[] = $row->subject_name; 
            $sub_dados[] = $row->knowledge_nivel;  
            $sub_dados[] = $row->user_nome; 
            $sub_dados[] = $row->knowledge_rank; 
            $sub_dados[] = ($row->knowledge_active) ? 
            
            "<form method='POST' action='".route('ativarmentor', $row->knowledge_id)."'>".
                method_field('PATCH'). 
                @csrf_field().
            "<button type='submit' role='button' class='btn btn-warning' data-toggle='tooltip' title='Inativar Item'><i class='fa fa-times'></i></button> </span></button> </form>" : 

            "<form method='POST' action='".route('ativarmentor', $row->knowledge_id)."'>".
                method_field('PATCH').
                @csrf_field()."<button type='submit' role='button' class='btn btn-success' data-toggle='tooltip' title='Ativar Item'><i class='fa fa-check'></i></button> </button></form>";
            
            $sub_dados[] = "<a href='".route('knowledge.edit', $row->knowledge_id)."' role='button' class='btn btn-primary' data-toggle='tooltip' title='Alterar'><span class='glyphicon glyphicon-edit'></span></a>";
            $sub_dados[] = "<form method='POST' action='".route('knowledge.destroy', $row->knowledge_id)."'>".
                            method_field('DELETE').
                            csrf_field().
                            "<button type='submit' role='button' class='btn btn-danger' data-toggle='tooltip' title='Excluir Item'><span class='glyphicon glyphicon-trash'></span></button></form>";
            $dados[] = $sub_dados;
        }
        
        $output = array (
            "draw"  => intval($request->draw),
            "recordsTotal" => $this->TodosRegistros(), 
            "recordsFiltered" => $this->RegistrosFiltradosAdmin($request),
            "data" => $dados
        );
        echo json_encode($output);
    }
    
    public function CriarQueryAdmin(Request $request)
    {
        $assunto = Knowledge::select('subject_id')
                            ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                            ->join('usersubjects', 'subject_id', '=', 'fk_user_subject')->get();
        $assuntos = array();
        
        foreach ($assunto as $value) {
            $assuntos[] = $value->subject_id;
        }

        $this->knowledge = Knowledge::select('knowledge_id', 'subject_name','knowledge_nivel', 'user_nome', 'knowledge_active','knowledge_rank')
                ->join('users', 'user_id', '=', 'fk_knowledge_user')
                ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                        ->whereIn('subject_id', $assuntos);            
                //não pode aparecer mentores que ja estão conectados
        
        if($request->input('search.value') != null)
        {
            $this->knowledge->where('user_nome', 'like' ,'%', $request->input('search.value'));            
        }
        if($request->order!= null)
        {
            $this->knowledge->orderBy(array_get($this->order, $request->input('order.0.column')),
                                $request->input('order.0.dir'));
        }
        else
        {
            $this->knowledge->orderBy('knowledge_rank', 'desc');
        }
    }
    
    public function CriarDataTable(Request $request)
    {
        $this->CriarQuery($request);
        if($request->length != -1)
        {
            $this->knowledge->offset($request->start)->limit($request->length);
        }
        $query = $this->knowledge->get();
        return $query;
    }
    
    public function CriarDataTableAdmin(Request $request)
    {
        $this->CriarQueryAdmin($request);
        if($request->length != -1)
        {
            $this->knowledge->offset($request->start)->limit($request->length);
        }
        $query = $this->knowledge->get();
        return $query;
    }
    
    public function RegistrosFiltrados(Request $request)
    {
        $this->CriarQuery($request);
        $query = $this->knowledge->count();
        return $query;
    }
    
    public function RegistrosFiltradosAdmin(Request $request)
    {
        $this->CriarQueryAdmin($request);
        $query = $this->knowledge->count();
        return $query;
    }

    public function TodosRegistrosAdmin()
    {
        $knowledge = Knowledge::select('subject_id')
                            ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                            ->join('usersubjects', 'subject_id', '=', 'fk_user_subject')->count();
        return $knowledge;
    }
    


    public function TodosRegistros()
    {
        $knowledge = Knowledge::select('subject_id')
                            ->join('subjects', 'subject_id', '=', 'fk_knowledge_subject')
                            ->join('usersubjects', 'subject_id', '=', 'fk_user_subject')
                            ->where('fk_subject_user', Auth::user()->user_id)
                            ->where('fk_knowledge_user', '<>', Auth::user()->user_id)
                            ->where('knowledge_active', true)->count();
        return $knowledge;
    }
}