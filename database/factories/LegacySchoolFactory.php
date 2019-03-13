<?php

use App\Models\LegacyEducationNetwork;
use App\Models\LegacyInstitution;
use App\Models\LegacyUser;
use Faker\Generator as Faker;

$factory->define(App\Models\LegacySchool::class, function (Faker $faker) {
    return [
        'ref_usuario_cad' => factory(LegacyUser::class)->state('unique')->make(),
        'ref_cod_instituicao' => factory(LegacyInstitution::class)->state('unique')->make(),
        'ref_cod_escola_rede_ensino' => factory(LegacyEducationNetwork::class)->create(),
        'sigla' => $faker->asciify(),
        'data_cadastro' => now(),
    ];
});
