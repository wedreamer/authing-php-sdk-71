<?php

namespace Authing\Mgmt\Roles;

use Error;
use stdClass;
use Exception;
use Authing\Types\Role;
use Authing\Types\UdvParam;
use Authing\Types\RoleParam;
use Authing\Types\RolesParam;
use Authing\Types\UDFDataType;
use Authing\Types\CommonMessage;
use Authing\Types\UDFTargetType;
use Authing\Types\PaginatedRoles;
use Authing\Types\PaginatedUsers;
use Authing\Types\RemoveUdvParam;
use Authing\Types\AssignRoleParam;
use Authing\Types\CreateRoleParam;
use Authing\Types\DeleteRoleParam;
use Authing\Types\RevokeRoleParam;
use Authing\Types\UpdateRoleParam;
use Authing\Types\DeleteRolesParam;
use Authing\Types\SetUdvBatchParam;
use Authing\Types\RoleWithUsersParam;
use Authing\Types\UdfValueBatchParam;
use Authing\Types\SetUdfValueBatchInput;
use Authing\Types\SetUdfValueBatchParam;
use Authing\Types\PolicyAssignmentsParam;
use Authing\Types\AddPolicyAssignmentsParam;
use Authing\Types\PaginatedPolicyAssignments;
use Authing\Types\PolicyAssignmentTargetType;
use Authing\Types\RemovePolicyAssignmentsParam;
use Authing\Types\ListRoleAuthorizedResourcesParam;

function formatAuthorizedResources($obj)
{
    $authorizedResources = $obj->authorizedResources;
    $list = $authorizedResources->list;
    $total = $authorizedResources->totalCount;
    array_map(function ($_) {
        foreach ($_ as $key => $value) {
            if (!$_->$key) {
                unset($_->$key);
            }
        }
        return $_;
    }, (array) $list);
    $res = new stdClass;
    $res->list = $list;
    $res->totalCount = $total;
    return $res;
}

function convertUdv(array $data)
{
    foreach ($data as $item) {
        $dataType = $item->dataType;
        $value = $item->value;
        if ($dataType === UDFDataType::NUMBER) {
            $item->value = json_encode($value);
        } else if ($dataType === UDFDataType::BOOLEAN) {
            $item->value = json_encode($value);
        } else if ($dataType === UDFDataType::DATETIME) {
            // set data time
            // $item->value = intval($value);
        } else if ($dataType === UDFDataType::OBJECT) {
            $item->value = json_encode($value);
        }
    }
    return $data;
}

function convertUdvToKeyValuePair(array $data)
{
    foreach ($data as $item) {
        $dataType = $item->dataType;
        $value = $item->value;
        if ($dataType === UDFDataType::NUMBER) {
            $item->value = json_encode($value);
        } else if ($dataType === UDFDataType::BOOLEAN) {
            $item->value = json_encode($value);
        } else if ($dataType === UDFDataType::DATETIME) {
            // set data time
            // $item->value = intval($value);
        } else if ($dataType === UDFDataType::OBJECT) {
            $item->value = json_encode($value);
        }
    }

    $ret = new stdClass();
    foreach ($data as $item) {
        $key = $item->key;
        $ret->$key = $item->value;
    }
    return $ret;
}

class RolesManagementClient
{
    /**
     * @var ManagementClient
     */
    private $client;

    /**
     * RolesManagementClient constructor.
     * @param $client ManagementClient
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * ??????????????????
     *
     * @param $page int ????????????
     * @param $limit int ????????????
     * @return PaginatedRoles
     * @throws Exception
     */
    public function paginate($page = 1, $limit = 10)
    {
        $param = (new RolesParam())->withPage($page)->withLimit($limit);
        return $this->client->request($param->createRequest());
    }

    /**
     * ????????????
     *
     * @param $code string ??????????????????
     * @param $description string ????????????
     * @param $parentCode string ?????????????????????
     * @return Role
     * @throws Exception
     */
    public function create($code, $description = null, $namespace = null)
    {
        $param = (new CreateRoleParam($code))->withDescription($description)->withNamespace($namespace);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ??????????????????
     * @return Role
     * @throws Exception
     */
    public function detail($code)
    {
        $param = new RoleParam($code);
        return $this->client->request($param->createRequest());
    }

    /**
     * @param string $code
     * @param string $namespace
     */
    public function findByCode($code, $namespace = '')
    {
        $param = (new RoleParam($code))->withNamespace($namespace);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ???????????? ID
     * @param $description string ????????????
     * @param $newCode string ?????????????????? ID
     * @return Role
     * @throws Exception
     */
    public function update($code, $description = null, $newCode = null)
    {
        $param = (new UpdateRoleParam($code))->withDescription($description)->withNewCode($newCode);
        return $this->client->request($param->createRequest());
    }

    /**
     * ????????????
     *
     * @param $code string ???????????? ID
     * @return CommonMessage
     * @throws Exception
     */
    public function delete($code)
    {
        $param = new DeleteRoleParam($code);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $codeList string[] ???????????? ID ??????
     * @return CommonMessage
     * @throws Exception
     */
    public function deleteMany($codeList)
    {
        $param = new DeleteRolesParam($codeList);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ??????????????????
     * @return PaginatedUsers
     * @throws Exception
     */
    public function listUsers($code)
    {
        $param = new RoleWithUsersParam($code);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ???????????? ID
     * @param $userIds string[] ?????? ID ??????
     * @return CommonMessage
     * @throws Exception
     */
    public function addUsers($code, $userIds)
    {
        $param = (new AssignRoleParam())->withUserIds($userIds)->withRoleCodes([$code]);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ???????????? ID
     * @param $userIds string[] ?????? ID ??????
     * @return CommonMessage
     * @throws Exception
     */
    public function removeUsers($code, $userIds)
    {
        $param = (new RevokeRoleParam())->withUserIds($userIds)->withRoleCodes([$code]);
        return $this->client->request($param->createRequest());
    }

    /**
     * ??????????????????
     *
     * @param $code string ???????????? ID
     * @param $page int ????????????
     * @param $limit int ????????????
     * @return PaginatedPolicyAssignments
     * @throws Exception
     */
    public function listPolicies($code, $page = 1, $limit = 10)
    {
        $param = (new PolicyAssignmentsParam())
            ->withPage($page)
            ->withLimit($limit)
            ->withTargetIdentifier($code)
            ->withTargetType(PolicyAssignmentTargetType::ROLE);
        return $this->client->request($param->createRequest());
    }

    /**
     * ????????????
     *
     * @param $code string ???????????? ID
     * @param $policies string[] ?????? ID ??????
     * @return CommonMessage
     * @throws Exception
     */
    public function addPolicies($code, $policies)
    {
        $param = (new AddPolicyAssignmentsParam($policies, PolicyAssignmentTargetType::ROLE))
            ->withTargetIdentifiers([$code]);
        return $this->client->request($param->createRequest());
    }

    /**
     * ????????????
     *
     * @param $code string ???????????? ID
     * @param $policies string[] ?????? ID ??????
     * @return CommonMessage
     * @throws Exception
     */
    public function removePolicies($code, $policies)
    {
        $param = (new RemovePolicyAssignmentsParam($policies, PolicyAssignmentTargetType::ROLE))
            ->withTargetIdentifiers([$code]);
        return $this->client->request($param->createRequest());
    }

    public function listAuthorizedResources($roleCode, $namespace, $opts = [])
    {
        $resourceType = null;
        if (count($opts) > 0) {
            $resourceType = $opts['resourceType'];
        }
        $param = (new ListRoleAuthorizedResourcesParam($roleCode))->withNamespace($namespace)->withResourceType($resourceType);
        $data = $this->client->request($param->createRequest());

        return formatAuthorizedResources($data);
    }

    /**
     * @param string $roleId
     */
    public function getUdfValue($roleId)
    {
        $param = (new UdvParam('ROLE', $roleId));
        $data = $this->client->request($param->createRequest());
        $list = $data->udv;
        return convertUdvToKeyValuePair($list);
    }

    /**
     * @param string $roleId
     * @param string $udfKey
     */
    public function getSpecificUdfValue($roleId, $udfKey)
    {
        $param = new UdvParam(UDFTargetType::ROLE, $roleId);
        $data = $this->client->request($param->createRequest())->udv;

        $udfMap = convertUdvToKeyValuePair($data);
        $udfValue = new stdClass();

        foreach ($udfMap as $key => $value) {
            if ($udfKey === $key) {
                $udfValue->$key = $value;
            }
        }

        return $udfValue;
    }

    public function getUdfValueBatch(array $roleIds)
    {
        if (count($roleIds) === 0) {
            throw new Error('empty user id list');
        }

        $param = new UdfValueBatchParam(UDFTargetType::ROLE, $roleIds);
        $data = $this->client->request($param->createRequest())->udfValueBatch;

        $ret = new stdClass();
        foreach ($data as $value) {
            $targetId = $value->targetId;
            $_data = $value->data;
            $ret->$targetId = convertUdvToKeyValuePair($data);
        }

        return $ret;
    }

    /**
     * @param string $roleId
     */
    public function setUdfValue($roleId, array $data)
    {
        if (count($data) === 0) {
            throw new Error('empty udf value list');
        }

        $param = (new SetUdvBatchParam(UDFTargetType::ROLE, $roleId))->withUdvList((object)$data);
        $this->client->request($param->createRequest());
    }

    public function setUdfValueBatch(array $input)
    {
        if (count($input) === 0) {
            throw new Error('empty input list');
        }
        $params = [];
        foreach ($input as $item) {
            $userId = $item->roleId;
            $data = $item->data;
            foreach ($data as $key => $value) {
                $param = new SetUdfValueBatchInput($userId, $key, $value);
                array_push($params, $param);
            }
        }
        $param = new SetUdfValueBatchParam(UDFTargetType::ROLE, $params);
        $this->client->request($param->createRequest());
    }

    /**
     * @param string $roleId
     * @param string $key
     */
    public function removeUdfValue($roleId, $key)
    {
        $param = new RemoveUdvParam(UDFTargetType::ROLE, $roleId, $key);
        $this->client->request($param->createRequest());
    }
}
