@extends('layouts.merge.site')
@section('titulo', 'Portal Oficial do INFOSI')
@section('content')


<section id="section_search" class="container-fluid">
    <div class="row my-3 mx-2 mx-md-5 gy-3 align-items-center">

        <div id="area_input" class="col-12 col-lg-9 col-xl-10 d-flex justify-content-center align-items-center shadow-sm">
            <i class="bi bi-search"></i>
            <input type="text" id="search" class="form-control" placeholder="O que procuras ? Ex.: servidor cloud, alojamento, certificado digital, homologação">
            <button class="btn">Pesquisar serviços</button>
        </div>

        <div id="area_information" class="col-12 col-lg-3 col-xl-2">
            <span class="icon_section">
                <i class="bi bi-bar-chart-line text-success"></i>
            </span>

            <div>
                <span>Todos os serviços operacionais</span>
                <span>Consulte o estado da plataforma</span>
            </div>
        </div>

    </div>
</section>

<section class="container-fluid">
    <!-- o estilo inline (height: 600px, background...) foi movido para .hero-banner no main.css -->
    <div class="hero-banner mx-2 mx-md-5 mt-2">

        <div class="d-flex justify-content-center text-center px-3">
            <div id="information_hero">
                <span id="logo_above">
                    <img src="/site/assets/favicon/BolaInfosi-removebg-preview.png" alt="" />
                    INFOSI Cloud Nacional
                </span>
                <div class="mt-3">
                    <h1>A infra-estrutura do Estado ao <br class="d-none d-lg-inline"/> serviço da sua instituição.</h1>
                    <p>Servidores Cloud, alojamento no Centro Nacional de Dados e certificação digital <br class="d-none d-lg-inline"/> com os dados em território nacional.</p>
                </div>
            </div>
        </div>

        <div class="container">
            <div id="pos_container">
                <div id="form_area">
                    <div id="line_form_area" class="row gy-3 align-items-center justify-content-center">

                        <div class="col-12 col-md-6 col-lg-3 item">
                            <label for="service">Serviço</label>
                            <select id="service">
                                <option>Servidor Cloud</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 item">
                            <label for="recursos">Recursos</label>
                            <select id="recursos">
                                <option>4 vCPU . 8 GB RAM</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 item">
                            <label for="sistema">Sistema</label>
                            <select id="sistema">
                                <option>Ubuntu 24.04 LTS</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 item except">
                            <button class="btn btn-dark">Começar agora <i class="bi bi-arrow-right"></i></button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<section id="institutional" class="container-fluid">
    <div class="row shadow py-3 mx-2 mx-md-5 mt-2 gy-3 row_institutional_section">

        <div class="col-12 col-md-6 col-lg-3 d-flex align-items-center gap-2">
            <div class="icon_session">
                <i class="bi bi-buildings"></i>
            </div>
            <div class="information_session">
                <span>Tutela do MINTTICS</span>
                <span>Instituto Público do Estado</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 d-flex align-items-center gap-2">
            <div class="icon_session">
                <i class="bi bi-hdd-stack"></i>
            </div>
            <div class="information_session">
                <span>Data Center do Governo</span>
                <span>Centro Nacional de Dados</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-center gap-2">
            <div class="icon_session">
                <i class="bi bi-alarm"></i>
            </div>
            <div class="information_session">
                <span>Operação 24 X 7</span>
                <span>Monitorização Permanente</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-center gap-2">
            <div class="icon_session">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="information_session">
                <span>Dados em Angola</span>
                <span>Soberania Garantida</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-center gap-2">
            <div class="icon_session">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div class="information_session">
                <span>Presença Nacional</span>
                <span>Em todas as províncias</span>
            </div>
        </div>

    </div>
</section>


<section class="container">
    <div class="d-flex align-items-center justify-content-center my-5">
        <div id="btns">
            <a href="#">Cloud e Alojamento</a>
            <a href="#">Registo de Empresa</a>
            <a href="#">Homologação de Software</a>
        </div>
    </div>


    <div id="hosting" class="row g-4 my-5 justify-content-center">

    <div class="col-12 col-md-6">
        <div class="hosting_item h-100 py-5 px-4">


            <div class="orange_area d-flex justify-content-center" id="left_side">
                <span class="text_above">
             <i class="bi bi-cloud"></i>  <span>aminhainstituicao.gov.ao</span>
            </span>
                <img src="/site/assets/images/IBMDALLASDC_10.avif" class="img-fluid"  alt="" />
            </div>

            <div class="text_area">
                <h4 class="my-3">Servidores Cloud</h4>
                <p>Máquinas virtuais com recursos dedicados, acesso root e escala <br class="d-none d-lg-inline"/> imediata, alojadas no Centro Nacional de Dados.</p>
                <a href="#">Ver planos cloud</a>
            </div>

        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="hosting_item h-100 py-5 px-4">

            <div class="orange_area" id="right_side">
                <img src="/site/assets/images/server-room-modern-data-center-.jpg" class="img-fluid" alt="" />

                  <span class="text_below">
                    <i class="bi bi-hdd-stack"></i>  <span>Plataforma dedicada</span>
                    </span>
            </div>

            <div class="text_area">
                <span class="disponibility">Disponível 24x7</span>
                <h4 class="my-3">Alojamento Data Center</h4>
                <p>Hosting para qualquer tipo de aplicação em plataformas dedicadas, partilhadas ou mista.</p>
                <a href="#">Pedir proposta</a>
            </div>

        </div>
    </div>

</div>
</section>


<section id="world_technology" class="container  my-5">
    <div>
        <h2 class="my-2">Os seus sistemas , online em minutos com a Cloud Nacional</h2>
        <div id="hero_img">
            <img src="/site/assets/images/Banner Globo IT.PNG" class="img-fluid" />
        </div>
    </div>
    <div class="boxes row">

        <div class="col-lg-4 col-md-6 col-sm-12">
            <h4>Crie o seu servidor</h4>
            <p>
                Escolha o sistema operativo e os recursos. O <br/>
                servidor fica pronto a usar com IP público, firewall <br/>
                 e acesso seguro.
            </p>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <h4>Escale quando precisar</h4>
            <p>
                Aumente CPU, memória e armazenamento à <br/>
                medida que o seu serviço cresce, sem migrar de <br/> plataforma.
            </p>
        </div>

         <div class="col-lg-4 col-md-6 col-sm-12">
            <h4>Monitorização Permanente</h4>
            <p>
               Equipa técnica do INFOSI a acompanhar a <br/> infra-estrutura  24 horas por dia com cópias de  <br/>
               segurança e réplica.
            </p>
        </div>

    </div>

       <div class="d-flex justify-content-center">
         <a href="#" class="my-5">Experimente a Cloud Nacional</a>
       </div>
    </div>
</section>

<section class="container-fluid my-5">
      <div class="text-center my-5">
        <h2>Conte com o INFOSI  todos os dias, a toda a hora</h2>

    </div>
    <div id="image_back">


        <div class="row">

            <div class="col-lg-5 col-md-6 col-sm-12  ">

                <div class="over_information">
            <h4>Os seus sistemas a correr no Centro Nacional de Dados,
                sob a lei angolana e acompanhados por técnicos angolanos.
            </h4>


            <ul>
                <li> <i class="bi bi-cloud"></i> Servidores Cloud</li>
                <li> <i class="bi bi-hdd-stack"></i> Alojamento dedicado, partilhado ou misto</li>
                <li> <i class="bi bi-award"></i> Certificados digitais e Carimbo do Tempo</li>
                <li> <i class="bi bi-router"></i> Rede Privativa do Estado</li>
            </ul>

             <div id="inside_container">
    <div class="row g-2">

        <div class="col-12 col-lg-6">
            <div class="box_item h-100">
                <span>Disponibilidade</span>
                <span>Operacional</span>
                <i class="bi bi-graph-up"></i>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="box_item h-100">
                <span>Cópias de Segurança</span>
                <span>Diárias</span>
                <i class="bi bi-bar-chart-fill"></i>
            </div>
        </div>

    </div>
</div>

        </div>



            </div>

        </div>



    </div>
</section>


<section class="container">
    <div class="text-center" id="why_work_with_us">
        <h2>Porquê trabalhar com o INFOSI ?</h2>
        <p>O Instituto Nacional de Fomento da Sociedade da Informação opera a infra-estrtura tecnlógica do Estado <br/> e garante a conformidade do sector das TIC em Angola.</p>
    </div>


    <div class="row g-4 my-5" id="why_us_item">

    {{-- 1. Homologação --}}
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="why-card">
            <p>Homologação de Software para sistemas usados no estado e no sector privado</p>

            <div id="homolog">
                <div class="header">
                    <span>Certificação de <br/> Homologação</span>
                    <i class="bi bi-check2"></i>
                </div>

                <small class="bar"></small>
                <small class="bar"></small>
                <small>Decreto Presidencial nº 135/21</small>
            </div>
        </div>
    </div>

    {{-- 2. Segurança --}}
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="why-card">
            <p>Segurança para dormir descansado: assinatura e certificação digital</p>

            <span id="lock_section" class="bg-white p-2">
                <i class="bi bi-lock p-2"></i> https://
            </span>
        </div>
    </div>

    {{-- 3. Carimbo do tempo --}}
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="why-card">
            <i class="bi bi-alarm"></i>
            <p>Carimbo do Tempo e Chaves Públicas que dão validade legal aos seus documentos e transacções</p>

            <span class="contract">
                <span>contrato_2026.pdf</span>
                <span>Assinado</span>
            </span>

            <span class="carimbo">
                <span>Carimbo do tempo</span>
                <span>07/10/2026 11:02:45</span>
            </span>
        </div>
    </div>

    {{-- 4. Registo de empresas --}}
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="why-card">
            <i class="bi bi-buildings"></i>
            <p>Registo de Empresas TIC com certificado de aptidão tecnológica para operar no sector</p>

            <small>Pedido online através do Digital.AO.</small>
        </div>
    </div>

    {{-- 5. Conectividade --}}
    <div class="col-lg-8 col-md-6 col-sm-12">
        <div class="why-card d-flex justify-content-center ">
           <div>
             <div>
                <i class="bi bi-broadcast-pin"></i>
                <p>Conectividade em todo o país, da Rede Privativa do Estado ao Kit Conecta Angola via ANGOSAT-2</p>
            </div>

            <small>E a regulação do mercado de domínios.ao, com a lista de revendedores autorizados.</small>


           </div>


           <div>
             <img src="/site/assets/images/satelite.png" class="img-fluid" />
           </div>
        </div>
    </div>

    {{-- Aviso --}}
    <p class="last_paragraph col-12">
        <span>Aviso:</span> imagens de painéis e equipamentos meramente ilustrativas. Os recursos e preços dos planos são definidos na proposta comercial.
    </p>

</div>

</section>


@endsection
