<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocatoria {{ $convocatoria->convocatoria }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 14pt;
            color: #666;
            margin-bottom: 5px;
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
        .orden-dia {
            margin: 20px 0;
        }
        .orden-dia ol {
            line-height: 2;
        }
        .firma {
            margin-top: 60px;
            text-align: right;
        }
        .firma-linea {
            width: 300px;
            border-top: 1px solid #333;
            margin: 50px 0 10px auto;
        }
        .firma-cargo {
            font-weight: bold;
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
    {{-- VºBº Presidente si está aprobado --}}
    @if($convocatoria->vb_presidente)
    <div class="vobo">
        ✓ VºBº PRESIDENTE
    </div>
    @endif

    {{-- Cabecera --}}
    <div class="header">
        {{-- Logo de la asociación (opcional) --}}
        {{-- <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo"> --}}
        
        <div class="title">CONVOCATORIA DE JUNTA</div>
        <div class="subtitle">{{ $convocatoria->asunto }}</div>
        <div class="subtitle">Nº {{ str_pad($convocatoria->convocatoria, 3, '0', STR_PAD_LEFT) }}/{{ $convocatoria->temporada->abreviatura }}</div>
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
    @if($convocatoria->vb_presidente)
        {{-- Dos firmas: VºBº Presidente (izquierda) + Cargo firmante (derecha) --}}
        <div style="display: table; width: 100%; margin-top: 60px;">
            <div style="display: table-cell; width: 48%; vertical-align: top; text-align: center;">
                {{-- VºBº Presidente --}}
                @if(file_exists(storage_path('app/firmas/presidente.png')))
                    <img src="{{ storage_path('app/firmas/presidente.png') }}" style="max-width: 150px; max-height: 80px; margin-bottom: 10px;">
                @else
                    <div style="height: 60px;"></div>
                @endif
                <div style="border-top: 1px solid #333; width: 200px; margin: 10px auto;"></div>
                <div style="font-weight: bold;">VºBº El Presidente</div>
            </div>
            <div style="display: table-cell; width: 4%;"></div>
            <div style="display: table-cell; width: 48%; vertical-align: top; text-align: center;">
                {{-- Cargo firmante --}}
                @php
                    $nombreArchivo = 'firma_cargo_' . $convocatoria->firma_cargo . '.png';
                    $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
                @endphp
                @if(file_exists($rutaFirma))
                    <img src="{{ $rutaFirma }}" style="max-width: 150px; max-height: 80px; margin-bottom: 10px;">
                @else
                    <div style="height: 60px;"></div>
                @endif
                <div style="border-top: 1px solid #333; width: 200px; margin: 10px auto;"></div>
                <div style="font-weight: bold;">
                    {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->nombre_cargo : 'El Firmante' }}
                </div>
            </div>
        </div>
    @else
        {{-- Una sola firma: Cargo firmante (derecha) --}}
        <div class="firma">
            @php
                $nombreArchivo = 'firma_cargo_' . $convocatoria->firma_cargo . '.png';
                $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
            @endphp
            @if(file_exists($rutaFirma))
                <img src="{{ $rutaFirma }}" style="max-width: 150px; max-height: 80px; margin-bottom: 10px;">
            @endif
            <div class="firma-linea"></div>
            <div class="firma-cargo">
                {{ $convocatoria->cargoFirmante ? $convocatoria->cargoFirmante->nombre_cargo : 'El Firmante' }}
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} - Convocatoria {{ $convocatoria->convocatoria }}/{{ $convocatoria->temporada->abreviatura }}</p>
    </div>
</body>
</html>