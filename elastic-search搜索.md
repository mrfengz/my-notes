-------- 聚合相关 -------------
sum/max/min/avg # 求和、最大、最小、平均
{
    "query" : {
        "constant_score" : {
            "filter" : {
                "match" : { "type" : "hat" }
            }
        }
    },
    "aggs" : {
        "hat_prices" : { "sum" : { "field" : "price" } }
    }
}


分时段 总用户（总点击次数）
订单总数统计：这个维度不同

----------------------



PUT /my_index
{
  "settings": {
    "number_of_shards": 1,
    "analysis": {
      "filter": {
        "my_shingle_filter": {
          "type": "shingle",
          "min_shingle_size": 2,
          "max_shingle_size": 2,
          "output_unigrams": false
        }
      },
      "analyzer": {
        "my_shingle_analyzer": {
          "type": "custom",
          "tokenizer": "standard",
          "filter": [
            "lowercase",
            "my_shingle_filter"
          ]
        }}}
  },
  "mappings": {
    "my_type": {
      "properties": {
        "type": "string",
        "fields": {
          "shingles": {
            "type": "string",
            "analyzer": "my_shingle_analyzer"
          }}}}}}


curl -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/august-test/_search?pretty'


curl -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_mapping?pretty'

add_to_cart
13411

click
11985

curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_search?size=3&pretty'

curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_search?size=10&pretty' -d '
{
	"query": {
	  	"range": {
	        "date": {
	            "gte": "2020-03-01",
	            "lte": "2020-04-26"
	        }
	    }
  	}
}		
'


 "query": {
  	"range": {
        "date": {
            "gte": "2020-03-01",
            "lte": "2020-04-26",
        }
    }
  },


curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_search?pretty' -d '
{
    "query": "SELECT * FROM library ORDER BY page_count DESC",
}'

curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/_sql/translate?pretty' -d '
{
    "query": "SELECT * FROM branch-test where date between '2020-04-01' and '2020-04-15'",
}'


curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_search?pretty' -d '
{
  	"size": 0,
  	"aggs": {
	    "group_date": {
	        "date_histogram": {
	            "field": "date",
	            "interval": "week",
	            "format": "yyyy-MM-dd",
	            "min_doc_count" :1, 
	            "extended_bounds" : { 
	                "min" : "2020-02-11",
	                "max" : "2020-04-22"
	            }
	        },
	        "aggs": {
		        "product_id": {
		            "terms": {
		            	"field": "product_id"
		          	}
		        },
		        "click_count": {
					"sum": {
						field: "click"
					}
		    	},
		    	"view_count": {
					"sum": {
						field: "click"
					}
		    	},
		    	"share_count": {
					"sum": {
						field: "click"
					}
		    	},
		    	"add_to_wishlist_count": {
					"sum": {
						field: "click"
					}
		    	}
	        }
	    }
    }
}
'

curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/_search?pretty' -d '
{
  "size": 0,
  "aggs": {
    "group_date": {
        "date_histogram": {
            "field": "date",
            "interval": "week",
            "format": "yyyy-MM-dd",
            "min_doc_count" :1, 
            "extended_bounds" : { 
                "min" : "2020-04-11",
                "max" : "2020-04-22"
            }
        },
        "aggs": {
	        "group_by_Type": {
	          "terms": {
	            "field": "entrance"
	          }
	        }
        }
    }
  }
}
'
curl -X POST -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/product_report/_search?pretty' -d '
{
  "size": 0,
  "query": {
            "bool": {
                "filter": {
                    "bool": {
                        "must": [
                            {
                                "terms": {
                                    "platform": [
                                        1,
                                        2
                                    ]
                                }
                            }
                        ]
                    }
                }
            }
        },
  "aggs": {
    "group_date": {
        "date_histogram": {
            "field": "date",
            "interval": "week",
            "format": "yyyy-MM-dd",
            "min_doc_count" :1, 
            "extended_bounds" : { 
                "min" : "2020-04-11",
                "max" : "2020-04-22"
            }
        },
         "aggs": {
	        "group_by_Type": {
	          "terms": {
	            "field": "entrance"
	          }
	        }
        }
    }
  }
}
'

{
    "index": "branch-test",
    "type": "product_report",
    "from": 0,
    "size": 50,
    "body": {
        "query": {
            "bool": {
                "filter": {
                    "bool": {
                        "must": [
                            {
                                "terms": {
                                    "platform": [
                                        "1",
                                        "2"
                                    ]
                                }
                            }
                        ]
                    }
                }
            }
        }
    }
}


curl -X PUT -H "Content-Type: application/json;charset=UTF-8" 'http://10.8.123.38:9200/branch-test/' -d '
{
  "mappings": {
    "visitor_id" : {
      "properties" : {
        "visitor_id" : {
          "type": "text",
          "fielddata": true
        }
      }
    }
  }
}
'

http://10.8.123.38:9200/branch-test/_search?pretty&size=1

## 时区转换
	date_hisgtram
	"time_zone": "+08:00"	# 加8个小时，东八区
	date类型可以存储多种格式，包括毫秒时间戳


## 限制请求的ip地址

## 简单sql转化
	_sql/translate 

#### 注意
	1. 还有特殊字符的文本，检索的时候，会被analize, 如果我们想要精确匹配，就需要设置 mapping（据说要先删除，后新增？？）
		"mappings" : {
	        "products" : {
	            "properties" : {
	                "productID" : {
	                    "type" : "string",
	                    "index" : "not_analyzed" 	# 设置为不分析
	                }
	            }
	        }
	    }
	2. 全局桶 global {}
	3. 返回空结果 min_doc_count
	4. 桶与指标
	5. post_filter 聚合结果过滤

## 基础概念
	
	Elastic 本质上是一个分布式数据库，允许多台服务器协同工作，每台服务器可以运行多个 Elastic 实例。
	
	1、Node与cluster
		单个 Elastic 实例称为一个节点（node）。一组节点构成一个集群（cluster）
	2、Index
		类似于数据库的作用，位于数据库管理的顶层单位。
		原理：反向索引（reversed index) ???
		curl -X POST 'http://localhost:9200/branch/indices?v'
	3、Document
		Index 里面单条的记录称为 Document（文档）。许多条 Document 构成了一个 Index。（也就是一行数据，一个记录）
	4、Type
		将Document分组，类似数据库的数据表的作用。一个Type中的字段类型应该一致	
		curl 'localhost:9200/branch?pretty=true' # 查看所有type		


基础命令请求：
	GET /megacorp/employee/_search	# 查询所有
	GET /megacorp/employee/_search?q=last_name:Smith 	# 简单过滤：查询名字为 Smith的用户
		领域特定语言：DSL，可以构造更为复杂的请求
			GET /megacorp/employee/_search
			{
			    "query" : {
			        "match" : {
			            "last_name" : "Smith"
			        }
			    }
			}

			{
			    "query" : {
			        "bool": {
			            "must": {
			            	<!-- 模糊匹配， 精确匹配使用：match_phrase -->
			                "match" : {
			                    "last_name" : "smith" 
			                }
			            },
			            <!-- 过滤器 -->
			            "filter": {
			                "range" : {
			                    "age" : { "gt" : 30 } 
			                }
			            }
			        }
			    },
			    <!-- 高亮显示文本 -->
			    "highlight": {
			        "fields" : {
			            "about" : {}
			        }
			    }
			}
			
			<!-- 聚合 -->
			1. 用intersects字段分组
			{
			  "aggs": {
			    "all_interests": {
			      "terms": { "field": "interests" }
			    }
			  }
			}
			2.对查询结果进行聚合
				{
				  "query": {
				    "match": {
				      "last_name": "smith"
				    }
				  },
				  "aggs": {
				    "all_interests": {
				      "terms": {
				        "field": "interests"
				      }
				    }
				  }
				}
			3.分级汇总
				{
				    "aggs" : {
				        "all_interests" : {
				            "terms" : { "field" : "interests" },
				            "aggs" : {
				                "avg_age" : {
				                	<!-- 求平均值 -->
				                    "avg" : { "field" : "age" }
				                }
				            }
				        }
				    }
				}	


## 新增
创建时不带有id，则会自动创建一个字符串id

curl -X PUT -H "Content-Type: application/json;charset=UTF-8" 'localhost:9200/branch/product/1' -d '
{
  "expressions": 1,
  "product_id": 1960581,
  "is_first": 1,
  "from_page": "home",
  "activity_type": "1",
  "uuid": 1658786
}' 

curl -X PUT -H "Content-Type: application/json;charset=UTF-8" 'localhost:9200/branch/product/2' -d '
{
  "expressions": 1,
  "product_id": 1960581,
  "is_first": 0,
  "from_page": "home",
  "activity_type": "2",
  "uuid": 1658786
}' 

curl -X PUT -H "Content-Type: application/json;charset=UTF-8" 'localhost:9200/branch/product/3' -d '
{
  "click": 1,
  "product_id": 1960581,
  "is_first": 0,
  "from_page": "category",
  "activity_type": "2",
  "uuid": 1658786
}' 

#### 查询
size: 查询记录数
from: 起始记录数据

# 查询记录总数
curl -XGET -H "Content-Type: application/json;charset=UTF-8" 'http://localhost:9200/_count?pretty' -d '
{
    "query": {
        "match_all": {}
    }
}
'

查询所有
curl 'localhost:9200/branch/product/_search'
	{
	    "took": 73,	# 耗时，毫秒
	    "timed_out": false,	# 是否超时
	    "_shards": {
	        "total": 1,
	        "successful": 1,
	        "skipped": 0,
	        "failed": 0
	    },
	    "hits": {	# 命中的记录
	        "total": {
	            "value": 3,
	            "relation": "eq"
	        },
	        "max_score": 1,
	        "hits": [
	            {
	                "_index": "branch",
	                "_type": "product",
	                "_id": "1",
	                "_score": 1,
	                "_source": {
	                    "expressions": 1,
	                    "product_id": 1960581,
	                    "is_first": 1,
	                    "from_page": "home",
	                    "activity_type": "1",
	                    "uuid": 1658786
	                }
	            },
	            {
	                "_index": "branch",
	                "_type": "product",
	                "_id": "2",
	                "_score": 1,
	                "_source": {
	                    "expressions": 1,
	                    "product_id": 1960581,
	                    "is_first": 0,
	                    "from_page": "home",
	                    "activity_type": "2",
	                    "uuid": 1658786
	                }
	            },
	            {
	                "_index": "branch",
	                "_type": "product",
	                "_id": "3",
	                "_score": 1,
	                "_source": {
	                    "click": 1,
	                    "product_id": 1960581,
	                    "is_first": 0,
	                    "from_page": "category",
	                    "activity_type": "2",
	                    "uuid": 1658786
	                }
	            }
	        ]
	    }
	}
查询单个
	curl 'localhost:9200/branch/product/3?pretty=true'
		found字段表示查询成功
		{
		  "_index" : "branch",
		  "_type" : "product",
		  "_id" : "1",
		  "_version" : 1,
		  "_seq_no" : 0,
		  "_primary_term" : 1,
		  "found" : true,
		  "_source" : {
		    "expressions" : 1,
		    "product_id" : 1960581,
		    "is_first" : 1,
		    "from_page" : "home",
		    "activity_type" : "1",
		    "uuid" : 1658786
		  }
		}

全文搜索
	单个检索条件
	curl -H "Content-Type: application/json;charset=UTF-8" 'localhost:9200/branch/product/_search' -d '{ "query" : { "match" : { "product_id" : "1960581"}}}'

	多个检索条件
		JSON='{ 
			"query" : {
				"bool": {
					"must" : [
						{ "match" : { "product_id" : "1960581"} },
						{ "match" : { "click" : "1"} },
					]
				}
			}
		}'
		curl -H "Content-Type: application/json;charset=UTF-8" 'localhost:9200/branch/product/_search' -d "${JSON}"


聚合
	修改字段定义，添加索引 （字符串类型的，必须要建立索引才能搜索）
	curl -X PUT "localhost:9200/branch/_mapping?pretty" -H 'Content-Type: application/json' -d'
		{
		  "properties": {
		    "from_page": { 
		      "type":     "text",
		      "fielddata": true
		    },
		    "activity_type": { 
		      "type":     "text",
		      "fielddata": true
		    }
		  }
		}
		'

		result:
			{
			  "acknowledged": true
			}


#### 删除
	curl -X DELETE 'localhost:9200/branch/product/1'
	{
	    "_index": "branch",
	    "_type": "product",
	    "_id": "XUqflXEB8DA5omK2_kfG",
	    "_version": 2,
	    "result": "deleted",
	    "_shards": {
	        "total": 2,
	        "successful": 1,
	        "failed": 0
	    },
	    "_seq_no": 4,
	    "_primary_term": 1
	}