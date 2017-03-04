import os,time, random, socket, datetime
import neo4j.v1

def createNodeCSV(i,depth):
    attributes = ['"name"', '"attr1"', '"attr2"', '"attr3"']
    ranges = {'"attr1"': [1.3, 4.5], '"attr2"': [1.3, 4.5], '"attr3"': [1.3, 4.5]}

    file = open('Nodi' + str(i) + '.csv', 'w')

    for i in range(0, len(attributes)):
        if i < len(attributes) - 1:
            file.write(attributes[i] + ',')
        elif i == len(attributes) - 1:
            file.write(attributes[i])
    file.write('\n')
    split_size = 2
    nodes = int((split_size ** (depth + 1) - 1) / (split_size - 1))
    start = time.clock()
    for i in range(1, nodes + 1):
        s = '"vertex_' + str(i) + '",'
        for j in range(1, len(attributes)):
            s = s + '"' + str(round(random.uniform(ranges[attributes[j]][0], ranges[attributes[j]][1]), 2)) + '",'
        s = s[:-1]
        file.write(s + '\n')
    return time.clock() - start

def createEdgeCSV(i,depth):
    attributes = ['"from"', '"to"', '"attr1"', '"attr2"', '"attr3"']
    ranges = {'"attr1"': [1.3, 4.5], '"attr2"': [1.3, 4.5], '"attr3"': [1.3, 4.5]}
    file = open('Archi' + str(i) + '.csv', 'w')

    for i in range(0, len(attributes)):
        if i < len(attributes) - 1:
            file.write(attributes[i] + ',')
        elif i == len(attributes) - 1:
            file.write(attributes[i])
    file.write('\n')
    start = time.clock()
    split_size = 2
    nodes = int((split_size ** (depth + 1) - 1) / (split_size - 1))
    for i in range(1, nodes - (split_size ** depth) + 1):
        for j in range(- split_size + 2, 2):
            s = '"vertex_' + str(i) + '","vertex_' + str(split_size * i + j) + '",'
            for j in range(2, len(attributes)):
                s = s + '"' + str(round(random.uniform(ranges[attributes[j]][0], ranges[attributes[j]][1]), 2)) + '",'
            s = s[:-1]
            file.write(s + '\n')
    return time.clock() - start

def child_request_sum(i, session):
    query = "Match p=(from:Vertex_0 {name:'vertex_2097151'})-[:EDGE_0*]->(to:Vertex_0 {name:'vertex_1'}) using index from:Vertex_0(name) using index to:Vertex_0(name) with nodes(p) as nod, relationships(p) as rels return nod, reduce(sum = 0, n IN nod| sum + n.attr1) as tot_attr1_node, reduce(sum = 0, n IN nod| sum + n.attr2) as tot_attr2_node, reduce(sum = 0, n IN nod| sum + n.attr3) as tot_attr3_node, reduce(sum = 0, n IN rels| sum + n.attr1) as tot_attr1_edge, reduce(sum = 0, n IN rels| sum + n.attr2) as tot_attr2_edge, reduce(sum = 0, n IN rels| sum + n.attr3) as tot_attr3_edge"
    start = time.clock()
    result = session.run(query)
    print('A new child ', os.getpid(), ' number ', i, ' ',time.clock() - start)
    for record in result:
        print(record['tot_attr1_node'])
        print(record['tot_attr2_node'])
        print(record['tot_attr3_node'])
        print(record['tot_attr1_edge'])
        print(record['tot_attr2_edge'])
        print(record['tot_attr3_edge'])
    os._exit(0)

def child_create(i, depth,session):
    print('\nA new child ', os.getpid(), ' number ', i)
    start = time.clock()
    sock = socket.socket()
    query = "Create index on :Vertex_" + str(i) + "(name)"
    sock.connect(('127.0.0.1', 5000))
    sock.send(query.encode())
    response = sock.recv(1024).decode()
    sock.close()
    createNodeCSV(i,depth)
    createEdgeCSV(i,depth)
    query = "Using periodic commit 40000 LOAD CSV WITH HEADERS FROM 'file:///Nodi"+ str(i) +".csv' AS line Create( :Vertex_"+ str(i) +" {name:line.name, attr1: toFloat(line.attr1), attr2: toFloat(line.attr2), attr3: toFloat(line.attr3)})"
    session.run(query)
    query = "Using periodic commit 40000 LOAD CSV WITH HEADERS FROM 'file:///Archi"+ str(i) +".csv' AS line \n Match (v_from:Vertex_"+ str(i) +" { name:line.from }) \n Match (v_to:Vertex_"+ str(i) +" { name:line.to }) \n Create( (v_from)<-[:EDGE_"+ str(i) +" {attr1: toFloat(line.attr1), attr2: toFloat(line.attr2), attr3: toFloat(line.attr3)}]-(v_to))"
    session.run(query)
    query = "Create (t:Tree {name:"+ str(i) +", split_size: "+ str(2) +", depth: "+ str(depth) +", tot_nodes: "+str(2**(depth +1) - 1)+", vertexAttrList:['attr1','attr2','attr3'], edgeAttrList:['attr1','attr2','attr3']})"
    session.run(query)
    session.close()
    os._exit(0)

def child_delete(i,session){
	sock = socket.socket()
    query = "Drop index on :Vertex_" + str(i) + "(name)"
    sock.connect(('127.0.0.1', 5000))
    sock.send(query.encode())
    response = sock.recv(1024).decode()
    sock.close()
    count = 1
    while count > 0:
    	query = "match (v:Vertex_" + str(i) + ") with v limit 50000 detach delete v return count(v) as count"
    	result = session.run(query)
    	for record in result:
    		count = record['count']			
    session.close()
    os._exit(0)
}    

def parent():
    driver = []
    pids =[]
    concurrency = input("Number of concurrecy for the simulation: ")
    depth = input("Depth of the trees the simulaton is going to create: ")
    now = datetime.datetime.now()
    print(str(now))
    for i in range (0,int(concurrency)):
        driver.append(neo4j.v1.GraphDatabase.driver("bolt://0.0.0.0:7687"))
        session = driver[i].session()
        newpid = os.fork()
        if newpid == 0:
            child_delete(i, session)
            #child_create(i,int(depth),session)
        else:
            pids.append(newpid)
    for pid in pids:
        child_pid, exit_status = os.waitpid(0, 0)
        print('\nChild ', child_pid, 'terminated')
    now = datetime.datetime.now()
    print(str(now))

parent()
