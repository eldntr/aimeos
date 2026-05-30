import json

with open('public/openapi.json', 'r') as f:
    data = json.load(f)

# Shop
data['paths']['/api/seller/shop'] = {
    'get': {
        'tags': ['Seller Profile'],
        'summary': 'Get shop profile',
        'security': [{'bearerAuth': []}],
        'responses': {
            '200': {'description': 'Successful response'}
        }
    },
    'put': {
        'tags': ['Seller Profile'],
        'summary': 'Update shop profile',
        'security': [{'bearerAuth': []}],
        'requestBody': {
            'content': {
                'multipart/form-data': {
                    'schema': {
                        'type': 'object',
                        'properties': {
                            'name': {'type': 'string'},
                            'logo': {'type': 'string', 'format': 'binary'},
                            'address': {'type': 'string'}
                        }
                    }
                }
            }
        },
        'responses': {
            '200': {'description': 'Successful response'}
        }
    }
}

# Bank
data['paths']['/api/seller/bank'] = {
    'put': {
        'tags': ['Seller Profile'],
        'summary': 'Update bank information',
        'security': [{'bearerAuth': []}],
        'requestBody': {
            'content': {
                'application/json': {
                    'schema': {
                        'type': 'object',
                        'properties': {
                            'bank_name': {'type': 'string'},
                            'account_number': {'type': 'string'},
                            'account_name': {'type': 'string'}
                        }
                    }
                }
            }
        },
        'responses': {
            '200': {'description': 'Successful response'}
        }
    }
}

# Variants
data['paths']['/api/seller/products/{id}/variants'] = {
    'get': {
        'tags': ['Seller Products'],
        'summary': 'Get product variants',
        'security': [{'bearerAuth': []}],
        'parameters': [{'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}],
        'responses': {
            '200': {'description': 'Successful response'}
        }
    },
    'post': {
        'tags': ['Seller Products'],
        'summary': 'Add product variant',
        'security': [{'bearerAuth': []}],
        'parameters': [{'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}],
        'requestBody': {
            'content': {
                'application/json': {
                    'schema': {
                        'type': 'object',
                        'properties': {
                            'code': {'type': 'string'},
                            'label': {'type': 'string'}
                        }
                    }
                }
            }
        },
        'responses': {
            '201': {'description': 'Variant created'}
        }
    }
}

data['paths']['/api/seller/products/{id}/variants/{variant_id}'] = {
    'delete': {
        'tags': ['Seller Products'],
        'summary': 'Delete product variant',
        'security': [{'bearerAuth': []}],
        'parameters': [
            {'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}},
            {'name': 'variant_id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}
        ],
        'responses': {
            '200': {'description': 'Successful response'}
        }
    }
}

# Images
data['paths']['/api/seller/products/{id}/images'] = {
    'get': {
        'tags': ['Seller Products'],
        'summary': 'Get product images',
        'security': [{'bearerAuth': []}],
        'parameters': [{'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}],
        'responses': {
            '200': {'description': 'Successful response'}
        }
    },
    'post': {
        'tags': ['Seller Products'],
        'summary': 'Upload product images',
        'security': [{'bearerAuth': []}],
        'parameters': [{'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}],
        'requestBody': {
            'content': {
                'multipart/form-data': {
                    'schema': {
                        'type': 'object',
                        'properties': {
                            'images[]': {
                                'type': 'array',
                                'items': {'type': 'string', 'format': 'binary'}
                            }
                        }
                    }
                }
            }
        },
        'responses': {
            '201': {'description': 'Images uploaded'}
        }
    }
}

data['paths']['/api/seller/products/{id}/images/{image_id}'] = {
    'delete': {
        'tags': ['Seller Products'],
        'summary': 'Delete product image',
        'security': [{'bearerAuth': []}],
        'parameters': [
            {'name': 'id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}},
            {'name': 'image_id', 'in': 'path', 'required': True, 'schema': {'type': 'string'}}
        ],
        'responses': {
            '200': {'description': 'Successful response'}
        }
    }
}

with open('public/openapi.json', 'w') as f:
    json.dump(data, f, indent=2)

