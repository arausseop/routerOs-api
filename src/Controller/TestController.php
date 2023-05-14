<?php

namespace App\Controller;

use App\Model\Exception\Router\RouterConnectionError;
use App\Service\Router\GetRouter;
use App\Service\Router\RouterFormProcessor;
use App\Service\Router\RouterManager;
use App\Service\TelnetRouterApi\RouterApi;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TestController extends AbstractController
{
    public $API = [], $router_data = [], $connection;

    #[Route('/test', name: 'app_test')]
    public function indexAction(): JsonResponse
    {
        try {
            return new JsonResponse([
                'success' => true,
                'message' => 'Welcome in Router API'
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetch data Router API'
            ]);
        }

        // return new JsonResponse(['message' => 'hola', 200]);
        // return $this->render('test/index.html.twig', [
        //     'controller_name' => 'TestController',
        // ]);
    }

    #[Route('/store-router', methods: ['POST'], name: 'app_store_router')]
    public function storeRouterAction(
        RouterFormProcessor $routerFormProcessor,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {

            [$router, $error] = ($routerFormProcessor)($request);

            $statusCode = $router ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $router ?? $error;

            if ($statusCode !== 201) {
                // return View::create($data, $statusCode);
                return JsonResponse::fromJsonString($data, $statusCode);
            } else {
                $data = $serializer->serialize($router, 'json', ['groups' => ['router']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (\Throwable $exception) {
            return JsonResponse::fromJsonString($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            // return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    // #[Rest\Get('/{uuid}')]
    // #[QueryParam(name: 'customerUuid', strict: false, nullable: true, allowBlank: true, description: 'search by customer uuid')]
    // public function getSingleAction(
    //     string $uuid,
    //     ?string $customerUuid,
    //     GetCompany $getCompany,
    //     GetCustomer $getCustomer,
    //     SerializerInterface $serializer,
    //     Request $request,
    //     ParamFetcherInterface $paramFetcher,
    // ) {
    //     $params = $paramFetcher->all();

    //     try {
    //         if ($this->isGranted("ROLE_ADMIN") || $this->isGranted("ROLE_SUPER_ADMIN")) {
    //             if (!$customerUuid) {
    //                 SystemNoParameterReceived::throwException('customerUuid');
    //             }
    //             $customer = ($getCustomer)($customerUuid);
    //         } else {
    //             $this->getUser();
    //             $customer = $this->getUser()->getCustomer();
    //         }

    //         $customerDbConfig = $customer->getCustomerDbConfig()[0];
    //         $switchEvent = new SwitchDbEvent($customerDbConfig->getId());
    //         $this->dispatcher->dispatch($switchEvent);

    //         $company = ($getCompany)($uuid, $customer->getId());

    //         $data = $serializer->serialize($company, 'json', ['groups' => ['company']]);
    //         return JsonResponse::fromJsonString($data);
    //     } catch (Exception $exception) {
    //         return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }
    #[Route('/check-router-connection/{uuid}',  name: 'app_check_router_connection')]
    public function checkRouterConnectionAction(
        string $uuid,
        GetRouter $getRouter,
        RouterManager $routerManager,
        SerializerInterface $serializer
    ) {
        try {

            $router = ($getRouter)($uuid);

            $connection  = $routerManager->checkRouterConnection($router->getIpAddress(), $router->getLogin(), $router->getPassword());
            if (!$connection) {
                RouterConnectionError::throwException();
            }

            $API = new RouterApi;
            $connection = $API->connect(
                $router->getIpAddress(), //'186.4.186.97:8728', 
                $router->getLogin(), // admin 
                $router->getPassword() //password
            );

            $identity = $API->comm('/ip/pool/print');
            // $identity = $API->comm('/user/active/print');
            $API->disconnect();

            dd($identity);

            $data = $serializer->serialize($router, 'json', ['groups' => ['router']]);

            return JsonResponse::fromJsonString($data);
        } catch (\Throwable $exception) {
            return JsonResponse::fromJsonString($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    // public function router_connection(Request $request)
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'ip_address' => 'required',
    //             'login' => 'required',
    //             'password' => 'required'
    //         ]);

    //         if ($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         $req_data = [
    //             'ip_address' => $request->ip_address,
    //             'login' => $request->login,
    //             'password' => $request->password
    //         ];

    //         $router_db = Router::where('ip_address', $req_data['ip_address'])->get();

    //         if (count($router_db) > 0) {
    //             if ($this->check_router_connection($request->all())) :
    //                 return new JsonResponse([
    //                     'connect' => true,
    //                     'message' => 'Router have a connection from database',
    //                     'router_data' => $this->router_data
    //                 ]);

    //             else :
    //                 return new JsonResponse([
    //                     'error' => true,
    //                     'message' => 'Router not connected, check administrator login !'
    //                 ]);
    //             endif;
    //         } else {
    //             return $this->store_router($request->all());
    //         }
    //     } catch (Exception $e) {
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, ' . $e->getMessage()
    //         ]);
    //     }
    // }

    // public function check_router_connection($data)
    // {
    //     $router_db = Router::where('ip_address', $data['ip_address'])->get();

    //     if(count($router_db) > 0):
    //         $API = new RouterAPI;
    //         $connection = $API->connect($router_db[0]['ip_address'], $router_db[0]['login'], $router_db[0]['password']);

    //         if($router_db[0]['connect'] !== $connection) $update_routerdb_connection = Router::where('id', $router_db[0]['id'])->update(['connect' => $connection]);

    //         if(!$connection) return false;

    //         $this->API = $API;
    //         $this->connection = $connection;
    //         $this->router_data = [
    //             'identity' => $this->API->comm('/system/identity/print')[0]['name'],
    //             'ip_address' => $router_db[0]['ip_address'],
    //             'login' => $router_db[0]['login'],
    //             'password' => Hash::make($router_db[0]['password']),
    //             'connect' => $connection
    //         ];
    //         return true;
    //     else:
    //         echo "Router data not available in database, please create connection again !";
    //     endif;
    // }

    // public function set_interface(Request $request)
    // {
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'ip_address' => 'required',
    //             'id' => 'required',
    //             'interface' => 'required',
    //             'name' => 'required'
    //         ]);

    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $interface_lists = $this->API->comm('/interface/print');
    //             $find_interface = array_search($request->name, array_column($interface_lists, 'name'));

    //             // var_dump($find_interface); die;

    //             if(!$find_interface):
    //                 $set_interface = $this->API->comm('/interface/set', [
    //                     '.id' => "*$request->id",
    //                     'name' => "$request->name"
    //                 ]);

    //                 return new JsonResponse([
    //                     'success' => true,
    //                     'message' => "Successfully set interface from : $request->interface, to : $request->name",
    //                     'interface_lists' => $this->API->comm('/interface/print')
    //                 ]);

    //             else:
    //                 return new JsonResponse([
    //                     'success' => false,
    //                     'message' => "Interface name : $request->name, with .id : *$request->id has already been taken from router",
    //                     'interface_lists' => $this->API->comm('/interface/print')
    //                 ]);
    //             endif;

    //         endif; 

    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }

    // public function add_new_address(Request $request)
    // {
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'ip_address' => 'required',
    //             'address' => 'required',
    //             'interface' => 'required'
    //         ]);

    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $interface_lists = $this->API->comm('/ip/address/print');

    //             $find_interface = array_search($request->interface, array_column($interface_lists, 'interface'));

    //             if($find_interface) return new JsonResponse(['error' => true, 'message' => "Interface $request->interface, have a such ip address on router", "suggestion" => "Did you want to editing ip address from interface : $request->interface", 'address_lists' => $this->API->comm('/ip/address/print')]);

    //             $add_address = $this->API->comm('/ip/address/add', [
    //                 'address' => $request->address,
    //                 'interface' => $request->interface
    //             ]);

    //             $list_address = $this->API->comm('/ip/address/print');

    //             $find_address_id = array_search($add_address, array_column($list_address, '.id'));

    //             if($find_address_id):
    //                 return new JsonResponse([
    //                     'success' => true,
    //                     'message' => "Successfully added new address from interface : $request->interface",
    //                     'address_lists' => $list_address
    //                 ]);
    //             else:
    //                 return new JsonResponse([
    //                     'success' => false,
    //                     'message' => $add_address['!trap'][0]['message'],
    //                     'address_lists' => $list_address,
    //                     'router_data' => $this->router_data
    //                 ]);
    //             endif;
    //         endif;

    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }

    // public function add_ip_route(Request $request)
    // {
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'ip_address' => 'required',
    //             'gateway' => 'required'
    //         ]);
    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $route_lists = $this->API->comm('/ip/route/print');
    //             $find_route_lists = array_search($request->gateway, array_column($route_lists, 'gateway'));

    //             if($find_route_lists === 0):
    //                 return new JsonResponse([
    //                     'success' => false,
    //                     'message' => "Gateway address : $request->gateway has already been taken",
    //                     'route_lists' => $this->API->comm('/ip/route/print')
    //                 ]);

    //             else:
    //                 $add_route_lists = $this->API->comm('/ip/route/add', [
    //                     'gateway' => $request->gateway
    //                 ]);
    //                 return new JsonResponse([
    //                     'success' => true,
    //                     'message' => "Successfully added new routes with gateway : $request->gateway",
    //                     'route_lists' => $this->API->comm('/ip/route/print'),
    //                     'router_data' => $this->router_data
    //                 ]);
    //             endif;

    //         endif;
    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }

    // public function add_dns_servers(Request $request)
    // {
    //     try{
    //         $schema = [
    //             'ip_address' => 'required',
    //             'servers' => 'required',
    //             'remote_requests' => 'required'
    //         ];

    //         $validator = Validator::make($request->all(), $schema);

    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $add_dns = $this->API->comm('/ip/dns/set', [
    //                 'servers' => $request->servers,
    //                 'allow-remote-requests' => $request->remote_requests
    //             ]);

    //             $dns_lists = $this->API->comm('/ip/dns/print');

    //             if(count($add_dns) == 0):
    //                 return new JsonResponse([
    //                     'success' => true,
    //                     'message' => 'Successfully addedd new dns servers',
    //                     'dns_lists' => $dns_lists
    //                 ]);
    //             else:
    //                 return new JsonResponse([
    //                     'success' => false,
    //                     'message' => 'Failed added dns servers',
    //                     'router_data' => $this->router_data
    //                 ]);
    //             endif;

    //         endif;

    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }

    // public function masquerade_srcnat(Request $request)
    // {
    //     try{
    //         $schema = [
    //             'ip_address' => 'required',
    //             'chain' => 'required',
    //             'protocol' => 'required',
    //             'out_interface' => 'required',
    //             'action' => 'required'
    //         ];
    //         $validator = Validator::make($request->all(), $schema);
    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $check_src_nat = $this->API->comm('/ip/firewall/nat/print');

    //             if(count($check_src_nat) == 0):
    //                 $add_firewall_nat = $this->API->comm('/ip/firewall/nat/add', [
    //                     'chain' => $request->chain,
    //                     'action' => $request->action,
    //                     'protocol' => $request->protocol,
    //                     'out-interface' => $request->out_interface
    //                 ]);

    //                 $firewall_nat_lists = $this->API->comm('/ip/firewall/nat/print');

    //                 return new JsonResponse([
    //                     'success' => true,
    //                     'message' => "Success added firewall nat for $request->chain",
    //                     'nat_lists' => $firewall_nat_lists
    //                 ]);
    //             else:
    //                 return new JsonResponse([
    //                     'error' => true,
    //                     'message' => "srcnat for out-interface $request->out_interface has already been taken",
    //                     'srcnat_lists' => $check_src_nat
    //                 ]);
    //             endif;
    //         endif;
    //     }catch(Exception $e){
    //         return new JsonResponse(['error' => true, 'message' => 'Error fetch router API '.$e->getMessage()]);
    //     }
    // }

    // public function router_reboot(Request $request)
    // {
    //     try{
    //         $schema = [
    //             'ip_address' => 'required'
    //         ];

    //         $validator = Validator::make($request->all(), $schema);

    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $reboot = $this->API->comm('/system/reboot');

    //             return new JsonResponse([
    //                 'reboot' => true,
    //                 'message' => 'Router has been reboot the system',
    //                 'connection' => $this->connection
    //             ]);

    //         endif;
    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }

    // public function router_shutdown(Request $request)
    // {
    //     try{
    //         $schema = [
    //             'ip_address' => 'required'
    //         ];

    //         $validator = Validator::make($request->all(), $schema);

    //         if($validator->fails()) return new JsonResponse($validator->errors(), 404);

    //         if($this->check_router_connection($request->all())):
    //             $update_connection = Router::where('ip_address', $request->ip_address)->update(['connect' => 0]);

    //             $new_router_data = Router::where('ip_address', $request->ip_address)->get();

    //             $shutdown = $this->API->comm('/system/shutdown');

    //             return new JsonResponse([
    //                 'shutdown' => true,
    //                 'message' => 'Router has been shutdown the system',
    //                 'connection' => $new_router_data[0]['connect']
    //             ]);

    //         endif;
    //     }catch(Exception $e){
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Error fetch data Router API, '.$e->getMessage()
    //         ]);
    //     }
    // }
}
