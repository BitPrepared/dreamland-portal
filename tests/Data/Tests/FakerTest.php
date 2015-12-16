<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 22/12/14 - 21:33.
 */
class FakerTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneraRagazzo()
    {
        // use the factory to create a Faker\Generator instance

        //@see https://github.com/fzaninotto/Faker
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $cc = [];
            $cc[] = $faker->firstName; // 'Lucy'
            $cc[] = $faker->lastName; // 'Curry'
            $cc[] = $faker->numberBetween(1, 26); //codicezona
            $cc[] = $faker->numberBetween(1, 9000); //codicegruppo
            $cc[] = $faker->randomLetter(); //regione
            $cc[] = $faker->randomNumber(5); //codicecensimento
            $cc[] = $faker->date($format = 'Ymd', $max = 'now');
            $cc[] = $faker->email(); //email casuale (anche non valida)
//            error_log(json_encode($cc),0,'stderr');
            unset($cc);
        }
    }
}
