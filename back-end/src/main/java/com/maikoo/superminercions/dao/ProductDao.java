package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.CustomerProductApplyDO;
import com.maikoo.superminercions.model.ProductDO;
import com.maikoo.superminercions.model.query.ProductQuery;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface ProductDao {
    @Insert("INSERT " +
            "INTO " +
                "c_user_product_apply" +
                "(c_user_id, comm_product_id, created_at) " +
            "VALUES" +
                "(#{customerDO.id}, #{productDO.id}, UNIX_TIMESTAMP(NOW()))")
    int insertCustomerProductApply(CustomerProductApplyDO customerProductApplyDO);

    @Results(id = "productDOResultMap", value = {
            @Result(property = "id", column = "id"),
            @Result(property = "imageUri", column = "image_uri"),
            @Result(property = "productNumber", column = "product_number"),
            @Result(property = "model", column = "model"),
            @Result(property = "name", column = "name"),
            @Result(property = "performance", column = "performance"),
            @Result(property = "price", column = "price"),
            @Result(property = "type", column = "type"),
            @Result(property = "createdAt", column = "created_at"),
            @Result(property = "updatedAt", column = "updated_at"),
            @Result(property = "deletedAt", column = "deleted_at"),
    })
    @Select("SELECT " +
                "id, " +
                "image_uri, " +
                "product_number, " +
                "model, " +
                "name, " +
                "performance, " +
                "price, " +
                "type " +
            "FROM " +
                "comm_product " +
            "WHERE " +
                "product_number = #{productNumber}")
    ProductDO selectOneByProductNumber(String productNumber);

    @ResultMap("productDOResultMap")
    @Select("SELECT " +
                "id, " +
                "image_uri, " +
                "product_number, " +
                "model, " +
                "name, " +
                "performance, " +
                "price, " +
                "type " +
            "FROM " +
                "comm_product " +
            "WHERE " +
                "id = #{id}")
    ProductDO selectOne(long id);

    @ResultMap("productDOResultMap")
    @Select("SELECT " +
                "id, " +
                "image_uri, " +
                "product_number, " +
                "model, " +
                "name, " +
                "price, " +
                "performance, " +
                "created_at " +
            "FROM " +
                "comm_product")
    List<ProductDO> selectAll();

    @Update("UPDATE comm_product " +
            "SET " +
                "model=#{minerModel}," +
                "name=#{minerName}," +
                "performance=#{minerCountForce}," +
                "price=#{minerPrice} ," +
                "updated_at= UNIX_TIMESTAMP(NOW())" +
            "where " +
                "product_number=#{minerSn}")
    int updateMiner(ProductQuery productQuery);

    @Update("UPDATE " +
                "comm_product " +
            "SET " +
                "performance = #{performance}, " +
                "price = #{price} " +
            "WHERE " +
                "id = #{id}")
    int update(ProductDO productDO);

    @Insert("INSERT " +
            "INTO " +
                "comm_product " +
                "(product_number, model, performance, name, price, created_at) " +
            " VALUES " +
                "(#{productNumber}, #{model}, #{performance}, #{name}, #{price}, UNIX_TIMESTAMP(NOW()))")
    int addMiner(ProductDO productDO);





}
