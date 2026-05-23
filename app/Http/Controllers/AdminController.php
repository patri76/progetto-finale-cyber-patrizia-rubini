<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Tag;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\HttpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    public function dashboard(){

        $adminRequests = User::where('is_admin', NULL)->get();
        $revisorRequests = User::where('is_revisor', NULL)->get();
        $writerRequests = User::where('is_writer', NULL)->get();

        try {

            // Effettua la richiesta HTTP
            $response = $this->httpService->getRequest('http://internal.finance:8001/user-data.php');

            // Controlla se la risposta è vuota o non valida
            if (empty($response)) {
                throw new Exception('La risposta dalla richiesta HTTP è vuota.');
            }

            // Decodifica il JSON
            $financialData = json_decode($response, true);

            // Controlla se ci sono errori nella decodifica del JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Errore nella decodifica del JSON: ' . json_last_error_msg());
            }

        } catch (Exception $e) {

            // Gestisci l'eccezione
            echo 'Errore: ' . $e->getMessage();

            // Registra errore nel log
            Log::error('Financial data error', [
                'message' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
        }

        return view('admin.dashboard', compact(
            'adminRequests',
            'revisorRequests',
            'writerRequests',
            'financialData'
        ));
    }
//modificato questo metodo
    public function setAdmin(User $user){

        $user->is_admin = true;

        Log::warning('ROLE CHANGE: user promoted to admin', [
            'target_user' => $user->email,
            'performed_by' => auth()->user()->email,
            'ip' => request()->ip(),
        ]);

        $user->save();

        return redirect(route('admin.dashboard'))
            ->with('message', "$user->name is now administrator");
    }

    public function setRevisor(User $user){

        $user->is_revisor = true;

        Log::warning('ROLE CHANGE: user promoted to revisor', [
            'target_user' => $user->email,
            'performed_by' => auth()->user()->email,
            'ip' => request()->ip(),
        ]);

        $user->save();

        return redirect(route('admin.dashboard'))
            ->with('message', "$user->name is now revisor");
    }

    public function setWriter(User $user){

        $user->is_writer = true;

        Log::warning('ROLE CHANGE: user promoted to writer', [
            'target_user' => $user->email,
            'performed_by' => auth()->user()->email,
            'ip' => request()->ip(),
        ]);

        $user->save();

        return redirect(route('admin.dashboard'))
            ->with('message', "$user->name is now writer");
    }

    public function editTag(Request $request, Tag $tag){

        $request->validate([
            'name' => 'required|unique:tags',
        ]);

        $tag->update([
            'name' => strtolower($request->name),
        ]);

        return redirect()->back()->with('message', 'Tag successfully updated');
    }

    public function deleteTag(Tag $tag){

        foreach($tag->articles as $article){
            $article->tags()->detach($tag);
        }

        $tag->delete();

        return redirect()->back()->with('message', 'Tag successfully deleted');
    }

    public function editCategory(Request $request, Category $category){

        $request->validate([
            'name' => 'required|unique:categories',
        ]);

        $category->update([
            'name' => strtolower($request->name),
        ]);

        return redirect()->back()->with('message', 'Category successfully updated');
    }

    public function deleteCategory(Category $category){

        $category->delete();

        return redirect()->back()->with('message', 'Category successfully deleted');
    }

    public function storeCategory(Request $request){

        $category = Category::create([
            'name' => strtolower($request->name),
        ]);

        return redirect()->back()->with('message', 'Category successfully created');
    }

    public function storeTag(Request $request){

        $tag = Tag::create([
            'name' => strtolower($request->name),
        ]);

        return redirect()->back()->with('message', 'Tag successfully created');
    }
}
//App\Models\User::create([
   // 'name' => 'Test Log User',
    //'email' => 'testlog@example.com',
   // 'password' => bcrypt('password'),
   // ]);

