<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocatoria {{ $convocatoria->convocatoria }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
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
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 14pt;
            color: #666;
            margin-bottom: 3px 0;
        }
        .info-block {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #333;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .content {
            margin: 30px 0;
            text-align: justify;
        }
        .vobo {
            position: absolute;
            top: 2cm;
            right: 2cm;
            padding: 10px 20px;
            border: 2px solid #4CAF50;
            background-color: #E8F5E9;
            font-weight: bold;
            color: #2E7D32;
            border-radius: 5px;
        }
        .firmas-container {
            margin-top: 60px;
        }
        .firma-bloque {
            text-align: center;
            margin-top: 20px;
        }
        .firma-imagen {
            max-width: 180px;
            max-height: 80px;
            margin-bottom: 5px;
        }
        .firma-linea {
            width: 220px;
            border-top: 1px solid #333;
            margin: 10px auto;
        }
        .firma-cargo {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 5px;
        }
        .firma-nombre {
            font-size: 10pt;
            color: #666;
            margin-top: 3px;
        }
        .footer {
            position: fixed;
            bottom: 1cm;
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
                    <div class="title">{{ $convocatoria->asunto }}</div>
                    <div class="subtitle">{{ $convocatoria->temporada->temporada }}</div>
                    <div class="subtitle">Nº {{ str_pad($convocatoria->convocatoria, 3, '0', STR_PAD_LEFT) }}/{{ $convocatoria->temporada->abreviatura }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Información de la junta --}}
    <div class="info-block">
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span>{{ \Carbon\Carbon::parse($convocatoria->fecha_junta)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Hora:</span>
            <span>{{ substr($convocatoria->hora1, 0, 5) }} a {{ substr($convocatoria->hora2, 0, 5) }} horas</span>
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
                        {{-- Firma VºBº Presidente --}}
                        @if(file_exists(storage_path('app/firmas/presidente.png')))
                            <img src="{{ storage_path('app/firmas/presidente.png') }}" class="firma-imagen">
                        @else
                            <div style="height: 60px;"></div>
                        @endif
                        <div class="firma-linea"></div>
                        <div class="firma-cargo">VºBº El Presidente</div>
                        @if(isset($config) && $config->nombre_presidente)
                            <div class="firma-nombre">{{ $config->nombre_presidente }}</div>
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
                            <img src="{{ $rutaFirma }}" class="firma-imagen">
                        @else
                            <div style="height: 60px;"></div>
                        @endif
                        <div class="firma-linea"></div>
                        <div class="firma-cargo">
                            {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->nombre_cargo : 'El Firmante' }}
                        </div>
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
                    <div class="firma-linea"></div>
                    <div class="firma-cargo">
                        {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->nombre_cargo : 'El Firmante' }}
                    </div>
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
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        @if(isset($config) && $config->nombre_asociacion)
            <p>{{ $config->nombre_asociacion }} - Documento generado el {{ now()->format('d/m/Y') }}</p>
        @else
            <p>Documento generado el {{ now()->format('d/m/Y') }} - Convocatoria {{ $convocatoria->convocatoria }}/{{ $convocatoria->temporada->abreviatura }}</p>
        @endif
    </div>
</body>
</html>