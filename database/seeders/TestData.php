<?php

namespace Database\Seeders;

use App\Http\Service\Image\SaveImage;
use App\Models\AboutUs;
use App\Models\AboutUsHome;
use App\Models\InfoContact;
use App\Models\Policies;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestData extends Seeder
{
    use SaveImage;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $about = AboutUs::create([
            'mission' => 'mision',
            'vision' => 'vision',
            'about_values' => ['valor1', 'valor2', 'valor3'],
            'name_yt' => 'nombre de yutu',
            'url_yt' => 'https://youtu.be/VdeH4HiokKQ'
        ]);

        $imageAbout = $this->saveImageUrl('https://sietefotografos.com/wp-content/uploads/2020/01/photo-1500964757637-c85e8a162699.jpeg');

        $about->images()->create([
            'url' => $imageAbout
        ]);

        $aboutHome = AboutUsHome::create([
            'text_section_one' => 'seccion 1',
            'text_section_two' => 'seccion 2',
        ]);

        $imageAboutHome = $this->saveImageUrl('https://sietefotografos.com/wp-content/uploads/2020/01/photo-1500964757637-c85e8a162699.jpeg');

        $aboutHome->images()->create([
            'url' => $imageAboutHome
        ]);

        InfoContact::create([
            'location' => 'Av. República de Panamá 2577, La Victoria',
            'cellphone' => '970300800',
            'email' => 'contacto@serfi.pe',
            'attention_hours' => 'Lunes a Viernes 8am. a 5pm'
        ]);

        $policies = Policies::create([
            'title' => 'titulo de politicas', 
            'description' => 'descripcion de politicas',
        ]);

        $imagePolicies = $this->saveImageUrl('https://sietefotografos.com/wp-content/uploads/2020/01/photo-1500964757637-c85e8a162699.jpeg');

        $policies->image()->create([
            'url' => $imagePolicies
        ]);
    }
}
