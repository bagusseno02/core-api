<?php

namespace coreapi\Utilities\Serializers;

use InvalidArgumentException;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\ResourceInterface;
use  League\Fractal\Serializer\ArraySerializer;

class JsonPaginatorApiSerializer extends ArraySerializer
{
    protected $baseUrl;
    protected $rootObjects;
    protected $relationshipsAsRootAttribute;

    /**
     * JsonApiSerializer constructor.
     *
     * @param bool $relationshipsAsRootAttribute
     * @param null $baseUrl
     */
    public function __construct($relationshipsAsRootAttribute=true, $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        $this->rootObjects = [];
        $this->relationshipsAsRootAttribute = $relationshipsAsRootAttribute;
    }

    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function collection(string $resourceKey, array $data): array
    {
        $resources = [];
        foreach ($data as $resource) {
            $resources[] = $this->item($resource);
        }
        return ['data' => $resources];
    }

    /**
     * Serialize an item.
     *
     * @param array $data
     *
     * @return array
     */
    public function item(array $data): array
    {
        return $data;
    }

    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator)
    {
        $currentPage = (int)$paginator->getCurrentPage();
        $lastPage = (int)$paginator->getLastPage();
        $pagination = [
            'total' => (int)$paginator->getTotal(),
            'count' => (int)$paginator->getCount(),
            'per_page' => (int)$paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        ];
        $pagination['links'] = [];
        $pagination['links']['self'] = $paginator->getUrl($currentPage);
        $pagination['links']['first'] = $paginator->getUrl(1);
        if ($currentPage > 1) {
            $pagination['links']['prev'] = $paginator->getUrl($currentPage - 1);
        }
        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }
        $pagination['links']['last'] = $paginator->getUrl($lastPage);
        return ['pagination' => $pagination];
    }

    /**
     * Serialize the meta.
     *
     * @param array $meta
     *
     * @return array
     */
    public function meta(array $meta)
    {
        if (empty($meta)) {
            return [];
        }
        $result['meta'] = $meta;
        if (array_key_exists('pagination', $result['meta'])) {
            $result['links'] = $result['meta']['pagination']['links'];
            unset($result['meta']['pagination']['links']);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function null()
    {
        return [
            'data' => null,
        ];
    }

    /**
     * Serialize the included data.
     *
     * @param ResourceInterface $resource
     * @param array $data
     *
     * @return array
     */
    public function includedData(ResourceInterface $resource, array $data)
    {
        if($this->relationshipsAsRootAttribute){
            return [];
        }

        list($serializedData, $linkedIds) = $this->pullOutNestedIncludedData($data);
        foreach ($data as $value) {
            foreach ($value as $includeObject) {
                if ($this->isNull($includeObject) || $this->isEmpty($includeObject)) {
                    continue;
                }
                $includeObjects = $this->createIncludeObjects($includeObject);
                list($serializedData, $linkedIds) = $this->serializeIncludedObjectsWithCacheKey($includeObjects,
                    $linkedIds, $serializedData);
            }
        }
        return empty($serializedData) ? [] : ['included' => $serializedData];
    }

    /**
     * Indicates if includes should be side-loaded.
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return true;
    }

    /**
     * @param array $data
     * @param array $includedData
     *
     * @return array
     */
    public function injectData($data, $includedData)
    {
        $relationships = $this->parseRelationships($includedData);
        if (!empty($relationships)) {
            $data = $this->fillRelationships($data, $relationships);
        }
        return $data;
    }

    /**
     * Hook to manipulate the final sideloaded includes.
     * The JSON API specification does not allow the root object to be included
     * into the sideloaded `included`-array. We have to make sure it is
     * filtered out, in case some object links to the root object in a
     * relationship.
     *
     * @param array $includedData
     * @param array $data
     *
     * @return array
     */
    public function filterIncludes($includedData, $data)
    {
        if (!isset($includedData['included'])) {
            return $includedData;
        }
        // Create the RootObjects
        $this->createRootObjects($data);
        // Filter out the root objects
        $filteredIncludes = array_filter($includedData['included'], [$this, 'filterRootObject']);
        // Reset array indizes
        $includedData['included'] = array_merge([], $filteredIncludes);
        return $includedData;
    }

    /**
     * Get the mandatory fields for the serializer
     *
     * @return array
     */
    public function getMandatoryFields()
    {
        return ['id'];
    }

    /**
     * Filter function to delete root objects from array.
     *
     * @param array $object
     *
     * @return bool
     */
    protected function filterRootObject($object)
    {
        return !$this->isRootObject($object);
    }

    /**
     * Set the root objects of the JSON API tree.
     *
     * @param array $objects
     */
    protected function setRootObjects(array $objects = [])
    {
        $this->rootObjects = array_map(function ($object) {
            $objectDate = isset($object['created_date']) ? $object['created_date'] : '-';
            $objectId = isset($object['id']) ? $object['id'] : '-';
            return "{$objectDate}:{$objectId}";
        }, $objects);
    }

    /**
     * Determines whether an object is a root object of the JSON API tree.
     *
     * @param array $object
     *
     * @return bool
     */
    protected function isRootObject($object)
    {

        $objectDate = isset($object['created_date']) ? $object['created_date'] : '-';
        $objectId = isset($object['id']) ? $object['id'] : '-';
        $objectKey = "{$objectDate}:{$objectId}";
        return in_array($objectKey, $this->rootObjects);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isCollection($data)
    {
        return array_key_exists('data', $data) &&
            array_key_exists(0, $data['data']);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isNull($data)
    {
        return array_key_exists('data', $data) && $data['data'] === null;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isEmpty($data)
    {
        return array_key_exists('data', $data) && $data['data'] === [];
    }

    /**
     * @param array $data
     * @param array $relationships
     *
     * @return array
     */
    protected function fillRelationships($data, $relationships)
    {
        if ($this->isCollection($data)) {
            foreach ($relationships as $key => $relationship) {
                $data = $this->fillRelationshipAsCollection($data, $relationship, $key);
            }
        } else { // Single resource
            foreach ($relationships as $key => $relationship) {
                $data = $this->fillRelationshipAsSingleResource($data, $relationship, $key);
            }
        }
        return $data;
    }

    /**
     * @param array $includedData
     *
     * @return array
     */
    protected function parseRelationships($includedData)
    {
        $relationships = [];
        foreach ($includedData as $key => $inclusion) {
            foreach ($inclusion as $includeKey => $includeObject) {
                $relationships = $this->buildRelationships($includeKey, $relationships, $includeObject, $key);
                if (isset($includedData[0][$includeKey]['meta'])) {
                    $relationships[$includeKey][0]['meta'] = $includedData[0][$includeKey]['meta'];
                }
            }
        }
        return $relationships;
    }

    /**
     * Keep all sideloaded inclusion data on the top level.
     *
     * @param array $data
     *
     * @return array
     */
    protected function pullOutNestedIncludedData(array $data)
    {
        $includedData = [];
        $linkedIds = [];
        foreach ($data as $value) {
            foreach ($value as $includeObject) {
                if (isset($includeObject['included'])) {
                    list($includedData, $linkedIds) = $this->serializeIncludedObjectsWithCacheKey($includeObject['included'],
                        $linkedIds, $includedData);
                }
            }
        }
        return [$includedData, $linkedIds];
    }

    /**
     * Whether or not the serializer should include `links` for resource objects.
     *
     * @return bool
     */
    protected function shouldIncludeLinks()
    {
        return $this->baseUrl !== null;
    }

    /**
     * Check if the objects are part of a collection or not
     *
     * @param $includeObject
     *
     * @return array
     */
    private function createIncludeObjects($includeObject)
    {
        if ($this->isCollection($includeObject)) {
            $includeObjects = $includeObject['data'];
            return $includeObjects;
        } else {
            $includeObjects = [$includeObject];
            return $includeObjects;
        }
    }

    /**
     * Sets the RootObjects, either as collection or not.
     *
     * @param $data
     */
    private function createRootObjects($data)
    {
        if ($this->isCollection($data)) {
            $this->setRootObjects($data['data']);
        } else {
            $this->setRootObjects([$data['data']]);
        }
    }

    /**
     * Loops over the relationships of the provided data and formats it
     *
     * @param $data
     * @param $relationship
     * @param $key
     *
     * @return array
     */
    private function fillRelationshipAsCollection($data, $relationship, $key)
    {
        if($this->relationshipsAsRootAttribute){
            foreach ($relationship as $index => $relationshipData) {
                $data['data'][$index][$key] = $relationshipData['data'];
                if ($this->shouldIncludeLinks()) {
                    $data['data'][$index][$key] = array_merge([
                        'links' => [
                            'self' => "{$this->baseUrl}/{$data['data'][$index]['type']}/{$data['data'][$index]['id']}/relationships/$key",
                            'related' => "{$this->baseUrl}/{$data['data'][$index]['type']}/{$data['data'][$index]['id']}/$key",
                        ],
                    ], $data['data'][$index][$key]);
                }
            }
        }else {
            foreach ($relationship as $index => $relationshipData) {
                $data['data'][$index]['relationships'][$key] = $relationshipData;
                if ($this->shouldIncludeLinks()) {
                    $data['data'][$index]['relationships'][$key] = array_merge([
                        'links' => [
                            'self' => "{$this->baseUrl}/{$data['data'][$index]['type']}/{$data['data'][$index]['id']}/relationships/$key",
                            'related' => "{$this->baseUrl}/{$data['data'][$index]['type']}/{$data['data'][$index]['id']}/$key",
                        ],
                    ], $data['data'][$index]['relationships'][$key]);
                }
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @param $relationship
     * @param $key
     *
     * @return array
     */
    private function fillRelationshipAsSingleResource($data, $relationship, $key)
    {
        if ($this->relationshipsAsRootAttribute){
            $data[$key] = $relationship[0]['data'];
            if ($this->shouldIncludeLinks()) {
                $data[$key] = array_merge([
                    'links' => [
                        'self' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/relationships/$key",
                        'related' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/$key",
                    ],
                ], $data[$key]);

                return $data;
            }
        } else {
            $data['data']['relationships'][$key] = $relationship[0];
            if ($this->shouldIncludeLinks()) {
                $data['data']['relationships'][$key] = array_merge([
                    'links' => [
                        'self' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/relationships/$key",
                        'related' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/$key",
                    ],
                ], $data['data']['relationships'][$key]);
                return $data;
            }
        }
        return $data;
    }

    /**
     * @param $includeKey
     * @param $relationships
     * @param $includeObject
     * @param $key
     *
     * @return array
     */
    private function buildRelationships($includeKey, $relationships, $includeObject, $key)
    {
        $relationships = $this->addIncludekeyToRelationsIfNotSet($includeKey, $relationships);
        
        if ($this->isNull($includeObject)) {
            $relationship = $this->null();
        } elseif ($this->isEmpty($includeObject)) {
            $relationship = [
                'data' => [],
            ];
        } elseif ($this->isCollection($includeObject)) {
            $relationship = ['data' => []];
            $relationship = $this->addIncludedDataToRelationship($includeObject, $relationship);
        } else {
            $relationship = [
                'data' => $this->relationshipsAsRootAttribute ? $includeObject : [$includeObject]
            ];
        }
        $relationships[$includeKey][$key] = $relationship;
        return $relationships;
    }

    /**
     * @param $includeKey
     * @param $relationships
     *
     * @return array
     */
    private function addIncludekeyToRelationsIfNotSet($includeKey, $relationships)
    {
        if (!array_key_exists($includeKey, $relationships)) {
            $relationships[$includeKey] = [];
            return $relationships;
        }
        return $relationships;
    }

    /**
     * @param $includeObject
     * @param $relationship
     *
     * @return array
     */
    private function addIncludedDataToRelationship($includeObject, $relationship)
    {
        foreach ($includeObject['data'] as $object) {
            $relationship['data'][] = $object;
        }
        return $relationship;
    }

    /**
     * @param $includeObjects
     * @param $linkedIds
     * @param $serializedData
     *
     * @return array
     */
    private function serializeIncludedObjectsWithCacheKey($includeObjects, $linkedIds, $serializedData)
    {
        foreach ($includeObjects as $object) {
            $includeType = isset($object['created_date']) ? $object['created_date'] : '-';
            $includeId = isset($object['id']) ? $object['id'] : '-';
            $cacheKey = "$includeType:$includeId";
            if (!array_key_exists($cacheKey, $linkedIds)) {
                $serializedData[] = $object;
                $linkedIds[$cacheKey] = $object;
            }
        }
        return [$serializedData, $linkedIds];
    }
}