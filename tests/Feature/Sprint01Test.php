<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use Database\Seeders\PerguntaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Sprint01Test extends TestCase
{
    use RefreshDatabase;

    public static function textosInvalidos(): array
    {
        return [
            'vazio' => [''],
            'apenas espaços' => ['          '],
            'nulo' => [null],
            'uma letra' => ['a'],
            'nove caracteres' => ['123456789'],
            'acima do máximo' => [str_repeat('a', 256)],
            'número' => [1234567890],
            'array' => [['pergunta inválida']],
        ];
    }

    #[DataProvider('textosInvalidos')]
    public function test_rejeita_textos_invalidos_com_422(mixed $texto): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $this->postJson(route('eventos.perguntas.store', $evento->id), [
            'evento_id' => $evento->id,
            'texto' => $texto,
        ])->assertStatus(422)->assertJsonValidationErrors('texto');

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_rejeita_campos_ausentes(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $this->postJson(route('eventos.perguntas.store', $evento->id), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['texto', 'evento_id']);

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_rejeita_evento_inexistente_no_corpo(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $this->postJson(route('eventos.perguntas.store', $evento->id), [
            'evento_id' => $evento->id + 100,
            'texto' => 'Como funciona a paginação?',
        ])->assertStatus(422)->assertJsonValidationErrors('evento_id');

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_aceita_os_limites_de_10_e_255_caracteres(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        foreach ([10, 255] as $tamanho) {
            $texto = str_repeat('a', $tamanho);

            $this->post(route('eventos.perguntas.store', $evento->id), [
                'evento_id' => $evento->id,
                'texto' => $texto,
            ])->assertRedirect(route('eventos.show', $evento->id))
                ->assertSessionHasNoErrors()
                ->assertSessionHas('sucesso');

            $this->assertDatabaseHas('perguntas', [
                'evento_id' => $evento->id,
                'texto' => $texto,
                'status' => 'pendente',
            ]);
        }

        $this->assertDatabaseCount('perguntas', 2);
    }

    public function test_formulario_invalido_preserva_texto_e_exibe_erro(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);
        $url = route('eventos.show', $evento->id);

        $this->from($url)->post(route('eventos.perguntas.store', $evento->id), [
            'evento_id' => $evento->id,
            'texto' => 'curto',
        ])->assertRedirect($url)->assertSessionHasErrors('texto');

        $this->get($url)->assertOk()->assertSee('is-invalid', false)->assertSee('curto');
        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_pagina_5000_perguntas_sem_misturar_eventos(): void
    {
        $this->seed(PerguntaSeeder::class);
        $evento = Evento::where('titulo', 'Palestra Principal: O Futuro da Computação em Nuvem')->firstOrFail();

        foreach ([1, 2, 500] as $pagina) {
            $response = $this->get(route('eventos.show', $evento->id).'?page='.$pagina);
            $response->assertOk();
            $perguntas = $response->viewData('perguntas');

            $this->assertInstanceOf(LengthAwarePaginator::class, $perguntas);
            $this->assertSame(5000, $perguntas->total());
            $this->assertSame(10, $perguntas->perPage());
            $this->assertCount(10, $perguntas->items());
            $this->assertSame($pagina, $perguntas->currentPage());
            $this->assertFalse($response->viewData('evento')->relationLoaded('perguntas'));

            foreach ($perguntas->items() as $indice => $pergunta) {
                $numero = 5000 - (($pagina - 1) * 10) - $indice;
                $this->assertSame($evento->id, $pergunta->evento_id);
                $this->assertStringStartsWith("Pergunta de teste #{$numero}:", $pergunta->texto);
            }

            $response->assertSee('class="pagination"', false)
                ->assertDontSee('Pergunta do workshop')
                ->assertSee('name="evento_id" value="'.$evento->id.'"', false);
        }
    }

    public function test_desempata_datas_iguais_por_id(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);
        $this->freezeTime();
        $ids = [];

        for ($i = 0; $i < 11; $i++) {
            $ids[] = Pergunta::create([
                'evento_id' => $evento->id,
                'texto' => 'Pergunta com data igual '.$i,
            ])->id;
        }

        $primeira = $this->get(route('eventos.show', $evento->id))->assertOk();
        $segunda = $this->get(route('eventos.show', $evento->id).'?page=2')->assertOk();

        $this->assertSame(array_slice(array_reverse($ids), 0, 10), $primeira->viewData('perguntas')->pluck('id')->all());
        $this->assertSame([$ids[0]], $segunda->viewData('perguntas')->pluck('id')->all());
    }

    public function test_evento_sem_perguntas_e_evento_inexistente(): void
    {
        $evento = Evento::create(['titulo' => 'Evento vazio']);

        $this->get(route('eventos.show', $evento->id))->assertOk()
            ->assertSee('Nenhuma pergunta enviada ainda.');
        $this->get(route('eventos.show', $evento->id + 100))->assertNotFound();
    }
}
