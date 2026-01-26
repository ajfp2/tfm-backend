<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocatoria {{ $convocatoria->convocatoria }}</title>
    <style>
        @page {
            margin: 0.7cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .header {
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #333;
        }
        .header table {
            border-collapse: collapse;
        }
        .header table td {
            padding: 0;
            border: none;
        }
        .logo {
            max-width: 150px;
            max-height: 150px;
            display: block;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0 2px 0;
            text-transform: uppercase;
        }
        .subtitle1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px 0;
        }
        .subtitle2 {
            font-size: 12pt;
            color: #666;
            margin-bottom: 3px 0;
        }
        .info-block {
            margin: 15px 0;
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-left: 5px solid #333;
        }
        .info-row {
            margin: 4px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .right {
            text-align: right;
        }
        .content {
            margin: 20px 15px;
            text-align: justify;
        }

        .firmas-container {
            margin-top: 40px;
        }
        .firma-bloque {
            text-align: center;
            margin-top: 20px;
        }
        .firma-imagen {
            max-width: 230px;
            max-height: 150px;
            margin-bottom: 5px;
        }

        .firma-cargo {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 3px;
        }
        .firma-nombre {
            font-size: 12pt;            
            margin-top: 5px;
            color: #555;
        }
        .footer {
            position: fixed;
            bottom: 0.4cm;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #999;
        }
    </style>
</head>
<body>

    {{-- Cabecera con Logo a la izquierda y textos a la derecha --}}
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 30%; vertical-align: middle; text-align: left;">
                    {{-- Logo de la configuración --}}
                    @if(isset($config) && $config->logo)
                        @php
                            // Extraer el path relativo desde la URL completa
                            if (str_starts_with($config->logo, 'http')) {
                                $parts = explode('/storage/', $config->logo);
                                $logoRelativePath = end($parts);
                            } else {
                                $logoRelativePath = $config->logo;
                            }
                            
                            $logoPath = storage_path('app/public/' . $logoRelativePath);
                        @endphp
                        @if(file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="Logo" class="logo">
                        @endif
                    @endif
                </td>
                <td style="width: 70%; vertical-align: middle; text-align: right;">
                    <div class="title">{{ $config->titulo }}</div>
                    <div class="subtitle1">{{ $convocatoria->asunto }}</div>
                    <div class="subtitle2">{{ $convocatoria->temporada->temporada }}</div>
                    <div class="subtitle2">Nº {{ str_pad($convocatoria->convocatoria, 3, '0', STR_PAD_LEFT) }}/{{ $convocatoria->temporada->abreviatura }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Información de la junta --}}
    <div class="info-block">
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span class="right">{{ \Carbon\Carbon::parse($convocatoria->fecha_junta)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Hora:</span>
            @if($convocatoria->hora1 && $convocatoria->hora2)
                <span>A las {{ substr($convocatoria->hora1, 0, 5) }} horas en 1ª y a las {{ substr($convocatoria->hora2, 0, 5) }} horas en 2ª Convocatoria</span>
            @else
                <span>A las {{ substr($convocatoria->hora1, 0, 5) }} horas</span>
            @endif
        </div>
        <div class="info-row">
            <span class="label">Lugar:</span>
            <span>{{ $convocatoria->lugar }}</span>
        </div>
    </div>

    {{-- Contenido de la convocatoria --}}
    <div class="content">
        {!! nl2br(e($convocatoria->texto)) !!}
    </div>

    {{-- Firma(s) --}}
    <div class="firmas-container">
        @if($convocatoria->vb_presidente)
            {{-- Dos firmas: VºBº Presidente (izquierda) + Cargo firmante (derecha) --}}
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 48%; vertical-align: top;">
                    <div class="firma-bloque">
                        <div class="firma-cargo">VºBº El Presidente</div>
                        {{-- Firma VºBº Presidente --}}
                        @if(file_exists(storage_path('app/firmas/presidente.png')))
                            <img src="{{ storage_path('app/firmas/presidente.png') }}" class="firma-imagen">
                        @else
                            <div style="height: 60px;"></div>
                        @endif
                        
                        {{-- Buscar nombre del Presidente desde historial_cargos_directivos --}}
                        @php
                            // Buscar el cargo 'Presidente' en la tabla junta_directiva
                            $cargoPresidente = \DB::table('junta_directiva')
                                ->where('cargo', 'LIKE', '%President%')
                                ->first();
                            
                            $presidente = null;
                            if ($cargoPresidente) {
                                // Buscar la persona que ocupa ese cargo en esta temporada
                                $presidente = \DB::table('historial_cargos_directivos')
                                    ->join('socios_personas', 'historial_cargos_directivos.a_persona', '=', 'socios_personas.Id_Persona')
                                    ->where('historial_cargos_directivos.a_cargo', $cargoPresidente->id)
                                    ->where('historial_cargos_directivos.a_temporada', $convocatoria->fk_temporadas)
                                    ->select('socios_personas.Nombre', 'socios_personas.Apellidos')
                                    ->first();
                            }
                        @endphp

                        @if($presidente)
                            <div class="firma-nombre">{{ $presidente->Apellidos }}, {{ $presidente->Nombre }}</div>
                        @else
                            <div class="firma-nombre" style="color: #ccc; font-style: italic;">
                                (Presidente sin asignar)
                            </div>
                        @endif
                    </div>
                </div>
                <div style="display: table-cell; width: 4%;"></div>
                <div style="display: table-cell; width: 48%; vertical-align: top;">
                    <div class="firma-bloque">
                        {{-- Firma Cargo firmante --}}
                        @php
                            $nombreArchivo = 'firma_cargo_' . $convocatoria->firma_cargo . '.png';
                            $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
                        @endphp
                        @if(file_exists($rutaFirma))
                            <!-- <img src="https://picsum.photos/300/300" class="firma-imagen"> -->
                            <img src="{{ $rutaFirma }}" class="firma-imagen">
                        @else
                            <div style="height: 60px;"></div>
                        @endif
                                                
                        @if($convocatoria->cargoFirmante)
                            {{-- Buscar el nombre de la persona que ocupa este cargo en la temporada actual --}}
                            @php
                                $persona = \DB::table('historial_cargos_directivos')
                                    ->join('socios_personas', 'historial_cargos_directivos.a_persona', '=', 'socios_personas.Id_Persona')
                                    ->where('historial_cargos_directivos.a_cargo', $convocatoria->firma_cargo)
                                    ->where('historial_cargos_directivos.a_temporada', $convocatoria->fk_temporadas)
                                    ->select('socios_personas.Apellidos', 'socios_personas.Nombre')
                                    ->first();
                            @endphp
                            @if($persona)
                                <div class="firma-nombre">{{ $persona->Nombre }} {{ $persona->Apellidos }}</div>
                            @endif
                        @endif

                        <div class="firma-cargo">
                            {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->cargo : 'El Firmante' }}
                        </div>
                        
                    </div>
                </div>
            </div>
        @else
            {{-- Una sola firma: Cargo firmante (derecha) --}}
            <div style="text-align: right;">
                <div class="firma-bloque" style="display: inline-block;">
                    @php
                        $nombreArchivo = 'firma_cargo_' . $convocatoria->firma_cargo . '.png';
                        $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
                    @endphp
                    @if(file_exists($rutaFirma))
                        <img src="{{ $rutaFirma }}" class="firma-imagen">
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                                        
                    @if($convocatoria->cargoFirmante)
                        {{-- Buscar el nombre de la persona que ocupa este cargo en la temporada actual --}}
                        @php
                            $persona = \DB::table('historial_cargos_directivos')
                                ->join('socios_personas', 'historial_cargos_directivos.a_persona', '=', 'socios_personas.Id_Persona')
                                ->where('historial_cargos_directivos.a_cargo', $convocatoria->firma_cargo)
                                ->where('historial_cargos_directivos.a_temporada', $convocatoria->fk_temporadas)
                                ->select('socios_personas.Apellidos', 'socios_personas.Nombre')
                                ->first();
                        @endphp
                        @if($persona)
                            <div class="firma-nombre">{{ $persona->Nombre }} {{ $persona->Apellidos }}</div>
                        @endif                        
                    @endif
                    <div class="firma-cargo">
                        {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->nombre_cargo : 'El Firmante' }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        @if(isset($config) && $config->titulo)
            <p>Documento generado el {{ now()->format('d/m/Y H:m') }}  - {{ $config->titulo }} Convocatoria Nº {{ $convocatoria->convocatoria }}/{{ $convocatoria->temporada->abreviatura }}</p>
        @else
            <p>Documento generado el {{ now()->format('d/m/Y H:m') }} - Convocatoria Nº {{ $convocatoria->convocatoria }}/{{ $convocatoria->temporada->abreviatura }}</p>
        @endif
    </div>
</body>
</html>