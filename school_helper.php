<?php

function get_bataan_public_schools()
{
    return [
        "Abucay North Elementary School",
        "Abucay South Elementary School",
        "Bagac Elementary School",
        "Bagac National High School",
        "Balanga City National High School",
        "Bataan National High School",
        "Dinalupihan Elementary School",
        "Dinalupihan National High School",
        "Hermosa National High School",
        "Jose C. Payumo Jr. Memorial High School",
        "Limay National High School",
        "Llamao National High School",
        "Luacan National High School",
        "Mariveles National High School - Camaya",
        "Mariveles National High School - Poblacion",
        "Morong National High School",
        "Orani National High School",
        "Orion National High School",
        "Pilar National High School",
        "Samal National High School",
        "Tortugas National High School"
    ];
}

function is_valid_school_name($schoolName)
{
    return in_array($schoolName, get_bataan_public_schools(), true);
}
