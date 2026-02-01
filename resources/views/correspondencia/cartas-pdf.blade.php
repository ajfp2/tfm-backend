<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartas de Correspondencia</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        .carta {
            page-break-after: always;
            min-height: 24cm;
        }
        .carta:last-child {
            page-break-after: auto;
        }
        .header {
            margin-bottom: 30px;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 15px;
        }
        .asociacion-datos {
            font-size: 9pt;
            color: #666;
            line-height: 1.4;
        }
        .fecha {
            text-align: right;
            margin: 20px 0;
            font-size: 10pt;
        }
        .destinatario {
            margin: 30px 0 40px 0;
            min-height: 100px;
        }
        .destinatario-nombre {
            font-weight: bold;
            font-size: 12pt;
        }
        .destinatario-direccion {
            margin-top: 5px;
            line-height: 1.4;
        }
        .asunto {
            margin: 20px 0;
            font-weight: bold;
            text-decoration: underline;
        }
        .contenido {
            margin: 30px 0;
            text-align: justify;
        }
        .despedida {
            margin-top: 40px;
        }
        .firma {
            margin-top: 60px;
        }
        .firma-linea {
            width: 200px;
            border-top: 1px solid #333;
            margin: 50px 0 10px 0;
        }
        .pie {
            position: fixed;
            bottom: 1cm;
            width: 100%;
            font-size: 8pt;
            color: #999;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @foreach($correspondencia->destinatarios as $index => $destinatario)
    <div class="carta">
        {{-- Membrete --}}
        <div class="header">
            {{-- Logo de configuración --}}
            @if(isset($config) && $config->logo)
                @php
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
            
            <div class="asociacion-datos">
                @if(isset($config) && $config->titulo)
                    <strong>{{ strtoupper($config->titulo) }}</strong><br>
                @else
                    <strong>ASOCIACIÓN</strong><br>
                @endif
                @if(isset($config))
                    @if($config->direccion){{ $config->direccion }}<br>@endif
                    @if($config->cp || $config->poblacion){{ $config->cp }} {{ $config->poblacion }}<br>@endif
                    @if($config->telefono)Tel: {{ $config->telefono }}<br>@endif
                    @if($config->email)Email: {{ $config->email }}@endif
                @endif
            </div>
        </div>

        {{-- Fecha --}}
        <div class="fecha">
            {{ \Carbon\Carbon::parse($correspondencia->creado)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </div>

        {{-- Destinatario --}}
        <div class="destinatario">
            <div class="destinatario-nombre">
                {{ $destinatario->nombre }} {{ $destinatario->apellidos }}
            </div>
            <div class="destinatario-direccion">
                @if($destinatario->direccion)
                    {{ $destinatario->direccion }}<br>
                @endif
                @if($destinatario->cp || $destinatario->poblacion)
                    {{ $destinatario->cp }} {{ $destinatario->poblacion }}<br>
                @endif
                @if($destinatario->provincia)
                    {{ $destinatario->provincia }}
                @endif
            </div>
        </div>

        {{-- Asunto --}}
        <div class="asunto">
            Asunto: {{ $correspondencia->asunto }}
        </div>

        {{-- Contenido --}}
        <div class="contenido">
            {!! nl2br(e($correspondencia->texto)) !!}
        </div>

        {{-- Despedida --}}
        <div class="despedida">
            <p>Atentamente,</p>
        </div>

        {{-- Firma(s) --}}
        @if($correspondencia->firma_cargo)
            @if($correspondencia->vb_presidente)
                {{-- Dos firmas: VºBº Presidente (izquierda) + Cargo firmante (derecha) --}}
                <div style="display: table; width: 100%; margin-top: 40px;">
                    <div style="display: table-cell; width: 48%; vertical-align: top; text-align: center;">
                        {{-- VºBº Presidente --}}
                        @if(file_exists(storage_path('app/firmas/presidente.png')))
                            <img src="{{ storage_path('app/firmas/presidente.png') }}" style="max-width: 120px; max-height: 60px; margin-bottom: 10px;">
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                        <div style="border-top: 1px solid #333; width: 150px; margin: 10px auto;"></div>
                        <div style="font-weight: bold; font-size: 9pt;">VºBº El Presidente</div>
                        @if(isset($config) && $config->nombre_presidente)
                            <div style="font-size: 8pt; color: #666;">{{ $config->nombre_presidente }}</div>
                        @endif
                    </div>
                    <div style="display: table-cell; width: 4%;"></div>
                    <div style="display: table-cell; width: 48%; vertical-align: top; text-align: center;">
                        {{-- Cargo firmante --}}
                        @php
                            $nombreArchivo = 'firma_cargo_' . $correspondencia->firma_cargo . '.png';
                            $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
                        @endphp
                        @if(file_exists($rutaFirma))
                            <img src="{{ $rutaFirma }}" style="max-width: 120px; max-height: 60px; margin-bottom: 10px;">
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                        <div style="border-top: 1px solid #333; width: 150px; margin: 10px auto;"></div>
                        
                        {{-- Nombre de la persona que firma --}}
                        @php
                            $persona = \DB::table('historial_cargos_directivos')
                                ->join('socios_personas', 'historial_cargos_directivos.a_persona', '=', 'socios_personas.Id_Persona')
                                ->where('historial_cargos_directivos.a_cargo', $correspondencia->firma_cargo)
                                ->where('historial_cargos_directivos.a_temporada', $correspondencia->fk_temporadas)
                                ->select('socios_personas.Nombre', 'socios_personas.Apellidos')
                                ->first();
                        @endphp
                        @if($persona)
                            <div style="font-size: 9pt; color: #666;">{{ $persona->Nombre }} {{ $persona->Apellidos }}</div>
                        @endif
                        
                        {{-- Cargo --}}
                        <div style="font-weight: bold; font-size: 9pt; margin-top: 3px;">
                            {{ $correspondencia->cargoFirmante ? $correspondencia->cargoFirmante->nombre_cargo : 'El Firmante' }}
                        </div>
                    </div>
                </div>
            @else
                {{-- Una sola firma: Cargo firmante (derecha) --}}
                <div class="firma" style="text-align: right;">
                    <div style="display: inline-block; text-align: center;">
                        @php
                            $nombreArchivo = 'firma_cargo_' . $correspondencia->firma_cargo . '.png';
                            $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
                        @endphp
                        @if(file_exists($rutaFirma))
                            <img src="{{ $rutaFirma }}" style="max-width: 120px; max-height: 60px; margin-bottom: 10px;">
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                        <div style="border-top: 1px solid #333; width: 150px; margin: 10px auto;"></div>
                        
                        {{-- Nombre de la persona --}}
                        @php
                            $persona = \DB::table('historial_cargos_directivos')
                                ->join('socios_personas', 'historial_cargos_directivos.fk_persona', '=', 'socios_personas.id')
                                ->where('historial_cargos_directivos.fk_cargo', $correspondencia->firma_cargo)
                                ->where('historial_cargos_directivos.fk_temporada', $correspondencia->fk_temporadas)
                                ->select('socios_personas.Nombre', 'socios_personas.Apellidos')
                                ->first();
                        @endphp
                        @if($persona)
                            <div style="font-size: 9pt; color: #666;">{{ $persona->Nombre }} {{ $persona->Apellidos }}</div>
                        @endif
                        
                        {{-- Cargo --}}
                        <div style="font-weight: bold; font-size: 10pt; margin-top: 3px;">
                            {{ $correspondencia->cargoFirmante ? $correspondencia->cargoFirmante->nombre_cargo : 'El Firmante' }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Sin firma personalizada, solo texto genérico --}}
            <div class="firma" style="text-align: right;">
                <div style="display: inline-block;">
                    <div class="firma-linea"></div>
                    <div style="text-align: center;">La Junta Directiva</div>
                </div>
            </div>
        @endif

        {{-- Pie de página --}}
        <div class="pie">
            Carta {{ $index + 1 }} de {{ $correspondencia->destinatarios->count() }}
            @if(isset($config) && $config->titulo)
                - {{ $config->titulo }}
            @endif
        </div>
    </div>
    @endforeach
</body>
</html>